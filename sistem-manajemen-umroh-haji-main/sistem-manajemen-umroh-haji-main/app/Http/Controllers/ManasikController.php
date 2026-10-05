<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Enums\RegistrationStatus;
use App\Http\Requests\Manasik\ManasikRequest;
use App\Models\Guide;
use App\Models\ManasikSchedule;
use App\Models\Package;
use App\Models\Pilgrim;
use App\Models\Registration;
use App\Services\AccessContext;
use App\Services\ActivityLogService;
use App\Services\BranchContext;
use App\Services\ManasikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManasikController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activity,
        private readonly BranchContext $branchContext,
        private readonly AccessContext $access,
        private readonly ManasikService $manasik,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ManasikSchedule::class);

        $user = $request->user();

        $query = ManasikSchedule::query()
            ->with(['package', 'guide', 'attendances'])
            ->withCount('attendances');

        if ($user?->isStaff()) {
            $this->branchContext->scope($query);
        } elseif ($user?->isGuide()) {
            $query->where('guide_id', $this->access->guideId() ?? -1);
        } elseif ($user?->isPilgrim()) {
            $packageIds = Registration::query()
                ->where('pilgrim_id', $this->access->pilgrimId() ?? -1)
                ->select('package_id');
            $query->whereIn('package_id', $packageIds);
        } else {
            $query->whereRaw('1 = 0');
        }

        $schedules = $query->latest('date')->paginate(15)->withQueryString();

        return view('manasik.index', compact('schedules'));
    }

    public function create(): View
    {
        $this->authorize('create', ManasikSchedule::class);

        return view('manasik.create', $this->formData(new ManasikSchedule([
            'branch_id' => $this->branchContext->currentBranchId(),
        ])));
    }

    public function store(ManasikRequest $request): RedirectResponse
    {
        $this->authorize('create', ManasikSchedule::class);

        $data = $request->validated();
        $data['branch_id'] = $this->branchContext->currentBranchId();

        $schedule = ManasikSchedule::create($data);
        $this->activity->created($schedule, "Menambah jadwal manasik {$schedule->title}");

        return redirect()->route('manasik.index')->with('success', 'Jadwal manasik berhasil ditambahkan.');
    }

    public function show(ManasikSchedule $manasik): View
    {
        $this->authorize('view', $manasik);

        $manasik->load(['package', 'guide']);

        $attendances = $manasik->attendances()->pluck('status', 'pilgrim_id');

        $pilgrims = $this->eligiblePilgrims($manasik);

        $rekap = [
            AttendanceStatus::Present->value => 0,
            AttendanceStatus::Absent->value => 0,
            AttendanceStatus::Excused->value => 0,
        ];
        foreach ($pilgrims as $pilgrim) {
            $status = $attendances[$pilgrim->id] ?? null;
            $value = $status instanceof AttendanceStatus ? $status->value : AttendanceStatus::Absent->value;
            $rekap[$value] = ($rekap[$value] ?? 0) + 1;
        }

        return view('manasik.show', [
            'schedule' => $manasik,
            'pilgrims' => $pilgrims,
            'attendances' => $attendances,
            'rekap' => $rekap,
        ]);
    }

    public function edit(ManasikSchedule $manasik): View
    {
        $this->authorize('update', $manasik);

        return view('manasik.edit', $this->formData($manasik));
    }

    public function update(ManasikRequest $request, ManasikSchedule $manasik): RedirectResponse
    {
        $this->authorize('update', $manasik);

        $manasik->update($request->validated());
        $this->activity->updated($manasik, "Memperbarui jadwal manasik {$manasik->title}");

        return redirect()->route('manasik.index')->with('success', 'Jadwal manasik berhasil diperbarui.');
    }

    public function destroy(ManasikSchedule $manasik): RedirectResponse
    {
        $this->authorize('delete', $manasik);

        $manasik->delete();
        $this->activity->deleted($manasik, "Menghapus jadwal manasik {$manasik->title}");

        return redirect()->route('manasik.index')->with('success', 'Jadwal manasik berhasil dihapus.');
    }

    public function storeAttendance(Request $request, ManasikSchedule $manasik): RedirectResponse
    {
        $this->authorize('recordAttendance', $manasik);

        $rows = (array) $request->input('status', []);
        $this->manasik->saveAttendance($manasik, $rows);
        $this->activity->log('updated', "Menyimpan absensi manasik {$manasik->title}", $manasik);

        return redirect()->route('manasik.show', $manasik)->with('success', 'Absensi disimpan.');
    }

    /**
     * Pilgrims eligible for this schedule's attendance.
     */
    private function eligiblePilgrims(ManasikSchedule $schedule)
    {
        if ($schedule->package_id) {
            $pilgrimIds = Registration::query()
                ->where('package_id', $schedule->package_id)
                ->where('status', '!=', RegistrationStatus::Cancelled->value)
                ->select('pilgrim_id');

            return Pilgrim::query()->whereIn('id', $pilgrimIds)->orderBy('name')->get();
        }

        $pilgrimIds = Registration::query()
            ->where('branch_id', $schedule->branch_id)
            ->where('status', '!=', RegistrationStatus::Cancelled->value)
            ->select('pilgrim_id');

        return Pilgrim::query()->whereIn('id', $pilgrimIds)->orderBy('name')->get();
    }

    /**
     * Shared payload for the create/edit forms.
     *
     * @return array<string, mixed>
     */
    private function formData(ManasikSchedule $schedule): array
    {
        $packages = $this->branchContext->scope(Package::query())
            ->orderBy('name')->pluck('name', 'id')->all();

        $guides = $this->branchContext->scope(Guide::query())
            ->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all();

        return [
            'schedule' => $schedule,
            'packages' => $packages,
            'guides' => $guides,
        ];
    }
}
