<?php

namespace App\Services;

use App\Enums\AnnouncementAudience;
use App\Enums\AttendanceStatus;
use App\Enums\DocumentStatus;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Models\Announcement;
use App\Models\Commission;
use App\Models\ManasikSchedule;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Pilgrim;
use App\Models\Registration;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;

/**
 * Builds dashboard datasets per role. Every method is defensive: missing
 * profiles / empty branches yield zeroed metrics and empty chart series so the
 * views never crash and Chart.js always receives a valid (possibly empty) shape.
 */
class DashboardService
{
    public function __construct(
        private readonly AccessContext $access,
        private readonly BranchContext $branches,
    ) {}

    /** Last 12 months as ['2025-07' => 'Jul 25', ...]. */
    private function monthBuckets(): array
    {
        $buckets = [];
        $cursor = CarbonImmutable::now()->startOfMonth()->subMonths(11);
        for ($i = 0; $i < 12; $i++) {
            $key = $cursor->format('Y-m');
            $buckets[$key] = $cursor->translatedFormat('M y');
            $cursor = $cursor->addMonth();
        }

        return $buckets;
    }

    public function announcementsFor(User $user, int $limit = 5)
    {
        $audiences = [AnnouncementAudience::All->value];
        $audiences[] = match (true) {
            $user->isStaff() => AnnouncementAudience::Staff->value,
            $user->isAgent() => AnnouncementAudience::Agents->value,
            $user->isPilgrim() => AnnouncementAudience::Pilgrims->value,
            $user->isGuide() => AnnouncementAudience::Guides->value,
            default => AnnouncementAudience::All->value,
        };

        $query = Announcement::query()->live()->whereIn('audience', $audiences);

        if (! $user->isSuperAdmin() && $user->branch_id) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('branch_id')->orWhere('branch_id', $user->branch_id);
            });
        }

        return $query->latest('publish_at')->latest('id')->limit($limit)->get();
    }

    // ------------------------------------------------------------------ ADMIN
    public function forStaff(User $user): array
    {
        $regs = fn () => $this->branches->scope(Registration::query());
        $pays = fn () => $this->branches->scope(Payment::query());

        $totalPilgrims = $this->branches->scope(Pilgrim::query())->count();
        $activeRegs = $regs()->where('status', '!=', RegistrationStatus::Cancelled->value)->count();
        $omzet = (float) $pays()->sum('paid_amount');
        $outstanding = max(0, (float) $regs()->sum('total_price') - $omzet);

        // Payment status doughnut (per registration)
        $paymentStatusCounts = ['paid' => 0, 'partial' => 0, 'unpaid' => 0];
        $regs()->with('payments')->where('status', '!=', RegistrationStatus::Cancelled->value)
            ->get()->each(function (Registration $r) use (&$paymentStatusCounts) {
                $paymentStatusCounts[$r->paymentStatus()->value]++;
            });

        // Jamaah per package (top 8)
        $packages = $this->branches->scope(Package::query())
            ->withCount(['registrations as active_count' => fn ($q) => $q->where('status', '!=', RegistrationStatus::Cancelled->value)])
            ->orderByDesc('active_count')->limit(8)->get();

        // Registrations over 12 months
        $buckets = $this->monthBuckets();
        $monthly = array_fill_keys(array_keys($buckets), 0);
        $regs()->where('created_at', '>=', now()->startOfMonth()->subMonths(11))
            ->get(['created_at'])->each(function ($r) use (&$monthly) {
                $key = optional($r->created_at)->format('Y-m');
                if ($key && isset($monthly[$key])) {
                    $monthly[$key]++;
                }
            });

        // Upcoming departures
        $upcoming = $this->branches->scope(Package::query())
            ->whereNotNull('departure_date')
            ->whereDate('departure_date', '>=', today())
            ->orderBy('departure_date')->limit(5)->get();

        return [
            'stats' => [
                'pilgrims' => $totalPilgrims,
                'registrations' => $activeRegs,
                'omzet' => $omzet,
                'outstanding' => $outstanding,
            ],
            'paymentStatus' => [
                'labels' => [PaymentStatus::Paid->label(), PaymentStatus::Partial->label(), PaymentStatus::Unpaid->label()],
                'data' => [$paymentStatusCounts['paid'], $paymentStatusCounts['partial'], $paymentStatusCounts['unpaid']],
            ],
            'perPackage' => [
                'labels' => $packages->pluck('name')->toArray(),
                'data' => $packages->pluck('active_count')->toArray(),
                'packages' => $packages,
            ],
            'monthly' => [
                'labels' => array_values($buckets),
                'data' => array_values($monthly),
            ],
            'upcoming' => $upcoming,
            'announcements' => $this->announcementsFor($user),
        ];
    }

    // ------------------------------------------------------------------ AGENT
    public function forAgent(User $user): array
    {
        $agentId = $user->agent?->id;

        $regQuery = fn () => Registration::query()->where('agent_id', $agentId ?? -1);
        $referrals = $regQuery()->where('status', '!=', RegistrationStatus::Cancelled->value)->count();

        $commissionQuery = fn () => Commission::query()->where('agent_id', $agentId ?? -1);
        $totalCommission = (float) $commissionQuery()->sum('amount');
        $paidCommission = (float) $commissionQuery()->where('status', 'paid')->sum('amount');

        // Commission per month
        $buckets = $this->monthBuckets();
        $monthly = array_fill_keys(array_keys($buckets), 0.0);
        $commissionQuery()->where('created_at', '>=', now()->startOfMonth()->subMonths(11))
            ->get(['created_at', 'amount'])->each(function ($c) use (&$monthly) {
                $key = optional($c->created_at)->format('Y-m');
                if ($key && isset($monthly[$key])) {
                    $monthly[$key] += (float) $c->amount;
                }
            });

        // Payment status of their pilgrims
        $paymentStatusCounts = ['paid' => 0, 'partial' => 0, 'unpaid' => 0];
        $regQuery()->with('payments')->where('status', '!=', RegistrationStatus::Cancelled->value)
            ->get()->each(function (Registration $r) use (&$paymentStatusCounts) {
                $paymentStatusCounts[$r->paymentStatus()->value]++;
            });

        return [
            'stats' => [
                'referrals' => $referrals,
                'commission_total' => $totalCommission,
                'commission_paid' => $paidCommission,
                'commission_pending' => max(0, $totalCommission - $paidCommission),
            ],
            'monthly' => [
                'labels' => array_values($buckets),
                'data' => array_map(fn ($v) => round($v), array_values($monthly)),
            ],
            'paymentStatus' => [
                'labels' => [PaymentStatus::Paid->label(), PaymentStatus::Partial->label(), PaymentStatus::Unpaid->label()],
                'data' => [$paymentStatusCounts['paid'], $paymentStatusCounts['partial'], $paymentStatusCounts['unpaid']],
            ],
            'hasProfile' => $agentId !== null,
            'announcements' => $this->announcementsFor($user),
        ];
    }

    // ---------------------------------------------------------------- PILGRIM
    public function forPilgrim(User $user): array
    {
        $pilgrim = $user->pilgrim;

        $registration = $pilgrim
            ? Registration::with(['package', 'payments'])
                ->where('pilgrim_id', $pilgrim->id)
                ->where('status', '!=', RegistrationStatus::Cancelled->value)
                ->latest()->first()
            : null;

        $documents = $pilgrim ? $pilgrim->documents()->get() : collect();
        $docTotal = $documents->count();
        $docVerified = $documents->where('status', DocumentStatus::Verified)->count();

        $package = $registration?->package;
        $countdown = null;
        if ($package?->departure_date) {
            $countdown = max(0, today()->diffInDays(Carbon::parse($package->departure_date), false));
        }

        $totalPrice = (float) ($registration?->total_price ?? 0);
        $totalPaid = $registration ? $registration->totalPaid() : 0.0;

        $upcomingManasik = collect();
        if ($pilgrim && $package) {
            $upcomingManasik = ManasikSchedule::where('package_id', $package->id)
                ->whereDate('date', '>=', today())
                ->orderBy('date')->limit(5)->get();
        }

        return [
            'pilgrim' => $pilgrim,
            'registration' => $registration,
            'package' => $package,
            'payment' => [
                'total' => $totalPrice,
                'paid' => $totalPaid,
                'outstanding' => max(0, $totalPrice - $totalPaid),
                'percent' => percent($totalPaid, $totalPrice),
            ],
            'documents' => [
                'total' => $docTotal,
                'verified' => $docVerified,
                'percent' => percent($docVerified, $docTotal),
                'items' => $documents,
            ],
            'countdown' => $countdown,
            'manasik' => $upcomingManasik,
            'hasProfile' => $pilgrim !== null,
            'announcements' => $this->announcementsFor($user),
        ];
    }

    // ------------------------------------------------------------------ GUIDE
    public function forGuide(User $user): array
    {
        $guideId = $user->guide?->id;

        $regQuery = fn () => Registration::query()->where('guide_id', $guideId ?? -1)
            ->where('status', '!=', RegistrationStatus::Cancelled->value);
        $guidedCount = $regQuery()->count();

        $todayManasik = ManasikSchedule::where('guide_id', $guideId ?? -1)
            ->whereDate('date', today())
            ->withCount('attendances')->get();

        $upcoming = ManasikSchedule::where('guide_id', $guideId ?? -1)
            ->whereDate('date', '>=', today())
            ->orderBy('date')->limit(5)->get();

        // Attendance doughnut across this guide's schedules
        $attendanceCounts = ['present' => 0, 'absent' => 0, 'excused' => 0];
        ManasikSchedule::where('guide_id', $guideId ?? -1)
            ->with('attendances')->get()
            ->each(function (ManasikSchedule $s) use (&$attendanceCounts) {
                foreach ($s->attendances as $a) {
                    $key = $a->status?->value;
                    if ($key && isset($attendanceCounts[$key])) {
                        $attendanceCounts[$key]++;
                    }
                }
            });

        return [
            'stats' => [
                'guided' => $guidedCount,
                'today_manasik' => $todayManasik->count(),
                'total_present' => $attendanceCounts['present'],
            ],
            'todayManasik' => $todayManasik,
            'upcoming' => $upcoming,
            'attendance' => [
                'labels' => [AttendanceStatus::Present->label(), AttendanceStatus::Absent->label(), AttendanceStatus::Excused->label()],
                'data' => [$attendanceCounts['present'], $attendanceCounts['absent'], $attendanceCounts['excused']],
            ],
            'hasProfile' => $guideId !== null,
            'announcements' => $this->announcementsFor($user),
        ];
    }
}
