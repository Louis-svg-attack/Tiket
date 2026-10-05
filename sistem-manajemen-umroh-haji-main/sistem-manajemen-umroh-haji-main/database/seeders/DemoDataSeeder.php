<?php

namespace Database\Seeders;

use App\Enums\AnnouncementAudience;
use App\Enums\AttendanceStatus;
use App\Enums\CommissionStatus;
use App\Enums\DocumentStatus;
use App\Enums\EquipmentStatus;
use App\Enums\Gender;
use App\Enums\PackageStatus;
use App\Enums\PackageType;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\RegistrationStatus;
use App\Enums\Role;
use App\Models\Agent;
use App\Models\Airline;
use App\Models\Announcement;
use App\Models\BankAccount;
use App\Models\Branch;
use App\Models\Commission;
use App\Models\EquipmentDistribution;
use App\Models\Guide;
use App\Models\Hotel;
use App\Models\ManasikAttendance;
use App\Models\ManasikSchedule;
use App\Models\ManifestEntry;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Pilgrim;
use App\Models\Registration;
use App\Models\User;
use App\Services\DocumentService;
use App\Services\SettingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Deterministic, idempotent demo data so EVERY dashboard chart is populated.
 * Re-running this seeder will not duplicate rows (keyed by unique codes/emails).
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $branches = $this->seedBranches();
        $this->seedDemoAccounts($branches['jakarta']);
        $hotels = $this->seedHotels();
        $airlines = $this->seedAirlines();
        $this->seedBankAccounts($branches);
        $packages = $this->seedPackages($branches, $hotels, $airlines);
        $agents = $this->seedAgents($branches['jakarta']);
        $guides = $this->seedGuides($branches['jakarta']);
        $pilgrims = $this->seedPilgrims($branches['jakarta'], $agents);
        $this->seedRegistrationsAndMoney($packages, $pilgrims, $agents, $guides);
        $this->seedManasik($packages, $guides);
        $this->seedAnnouncements($branches['jakarta']);
    }

    private function seedSettings(): void
    {
        app(SettingService::class)->setMany([
            'app_name' => 'Travel Umroh & Haji',
            'app_subtitle' => 'Sistem Manajemen Umroh & Haji',
            'company_address' => 'Jl. Melati Indah No. 17, Jakarta Selatan',
            'company_phone' => '+62 21 7654 3210',
            'company_email' => 'info@example.com',
            'footer_text' => '© '.date('Y').' Travel Umroh & Haji — Sistem Manajemen Umroh & Haji.',
            'primary_color' => '#0d9488',
            'secondary_color' => '#0f766e',
        ]);
    }

    /** @return array<string, Branch> */
    private function seedBranches(): array
    {
        $jakarta = Branch::updateOrCreate(['code' => 'JKT'], [
            'name' => 'Kantor Pusat Jakarta', 'city' => 'Jakarta',
            'phone' => '021-7654321', 'email' => 'pusat@example.com',
            'address' => 'Jl. Melati Indah No. 17, Jakarta Selatan',
            'is_active' => true, 'is_head_office' => true,
        ]);

        $surabaya = Branch::updateOrCreate(['code' => 'SBY'], [
            'name' => 'Cabang Surabaya', 'city' => 'Surabaya',
            'phone' => '031-1234567', 'email' => 'surabaya@example.com',
            'address' => 'Jl. Pemuda No. 88, Surabaya',
            'is_active' => true, 'is_head_office' => false,
        ]);

        return ['jakarta' => $jakarta, 'surabaya' => $surabaya];
    }

    private function seedDemoAccounts(Branch $branch): void
    {
        $accounts = [
            ['superadmin@example.com', 'Super Admin', Role::SuperAdmin],
            ['admin@example.com', 'Admin Cabang', Role::Admin],
        ];

        foreach ($accounts as [$email, $name, $role]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => Hash::make('password'),
                'role' => $role->value,
                'branch_id' => $branch->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }
    }

    /** @return array<string, Hotel> */
    private function seedHotels(): array
    {
        $data = [
            'makkah_1' => ['Swissôtel Al Maqam Makkah', 'Mekah', 5, 100],
            'makkah_2' => ['Hilton Makkah Convention', 'Mekah', 5, 250],
            'makkah_3' => ['Anjum Hotel Makkah', 'Mekah', 4, 400],
            'madinah_1' => ['Anwar Al Madinah Mövenpick', 'Madinah', 5, 80],
            'madinah_2' => ['Dallah Taibah Hotel', 'Madinah', 4, 150],
            'madinah_3' => ['Al Eiman Royal Hotel', 'Madinah', 4, 300],
        ];

        $hotels = [];
        foreach ($data as $key => [$name, $city, $star, $dist]) {
            $hotels[$key] = Hotel::updateOrCreate(['name' => $name], [
                'city' => $city, 'star' => $star, 'distance_to_masjid' => $dist, 'is_active' => true,
            ]);
        }

        return $hotels;
    }

    /** @return array<int, Airline> */
    private function seedAirlines(): array
    {
        $data = [
            ['Saudi Arabian Airlines', 'SV', 'Langsung'],
            ['Garuda Indonesia', 'GA', 'Langsung'],
            ['Emirates', 'EK', 'Transit Dubai'],
        ];

        $airlines = [];
        foreach ($data as $i => [$name, $code, $transit]) {
            $airlines[$i] = Airline::updateOrCreate(['name' => $name], [
                'code' => $code, 'transit' => $transit, 'is_active' => true,
            ]);
        }

        return $airlines;
    }

    private function seedBankAccounts(array $branches): void
    {
        BankAccount::updateOrCreate(['account_number' => '7001234567'], [
            'branch_id' => $branches['jakarta']->id, 'bank_name' => 'Bank Syariah Indonesia',
            'account_holder' => 'PT Travel Umroh Haji', 'branch_office' => 'KCP Jakarta', 'is_active' => true,
        ]);
        BankAccount::updateOrCreate(['account_number' => '8009876543'], [
            'branch_id' => $branches['jakarta']->id, 'bank_name' => 'Bank Muamalat',
            'account_holder' => 'PT Travel Umroh Haji', 'branch_office' => 'KCP Jakarta', 'is_active' => true,
        ]);
    }

    /** @return array<int, Package> */
    private function seedPackages(array $branches, array $hotels, array $airlines): array
    {
        $defs = [
            ['PKG-UMR9', 'Umroh Reguler 9 Hari', PackageType::Umroh, 28500000, 5000000, 45, 9, 1, PackageStatus::Open],
            ['PKG-UMR12', 'Umroh Plus Turki 12 Hari', PackageType::Umroh, 36500000, 7500000, 40, 12, 2, PackageStatus::Open],
            ['PKG-HAJ', 'Haji Khusus 2026', PackageType::Haji, 125000000, 25000000, 30, 40, 3, PackageStatus::Open],
            ['PKG-HAJP', 'Haji Plus Eksklusif', PackageType::HajiPlus, 165000000, 35000000, 20, 35, 5, PackageStatus::Open],
            ['PKG-UMRA', 'Umroh Akhir Tahun', PackageType::Umroh, 31000000, 6000000, 50, 10, 0, PackageStatus::Open],
        ];

        $packages = [];
        foreach ($defs as $i => [$code, $name, $type, $price, $dp, $quota, $days, $monthOffset, $status]) {
            $branch = $i === 4 ? $branches['surabaya'] : $branches['jakarta'];
            $packages[$i] = Package::updateOrCreate(['code' => $code], [
                'branch_id' => $branch->id,
                'name' => $name,
                'type' => $type->value,
                'price' => $price,
                'down_payment' => $dp,
                'quota' => $quota,
                'duration_days' => $days,
                'departure_date' => Carbon::today()->addMonths($monthOffset + 1)->startOfMonth()->addDays(9),
                'departure_city' => $branch->city,
                'hotel_makkah_id' => $hotels['makkah_'.(($i % 3) + 1)]->id,
                'hotel_madinah_id' => $hotels['madinah_'.(($i % 3) + 1)]->id,
                'airline_id' => $airlines[$i % 3]->id,
                'facilities_included' => ['Tiket pesawat PP', 'Hotel bintang 4/5', 'Visa & manasik', 'Makan 3x sehari', 'Bus AC'],
                'facilities_excluded' => ['Pengeluaran pribadi', 'Kelebihan bagasi', 'Vaksin'],
                'description' => 'Paket perjalanan ibadah dengan bimbingan muthawif berpengalaman.',
                'status' => $status->value,
            ]);
        }

        return $packages;
    }

    /** @return array<int, Agent> */
    private function seedAgents(Branch $branch): array
    {
        $defs = [
            ['AG-DEMO1', 'Hasan Basri', 'agent@example.com', 7.5, true],
            ['AG-DEMO2', 'Siti Aminah', 'agent2@example.com', 5.0, true],
            ['AG-DEMO3', 'Rahmat Hidayat', 'agent3@example.com', 6.0, false],
        ];

        $agents = [];
        foreach ($defs as $i => [$code, $name, $email, $rate, $withAccount]) {
            $userId = null;
            if ($withAccount) {
                $user = User::updateOrCreate(['email' => $email], [
                    'name' => $name, 'password' => Hash::make('password'),
                    'role' => Role::Agent->value, 'branch_id' => $branch->id,
                    'is_active' => true, 'email_verified_at' => now(),
                ]);
                $userId = $user->id;
            }

            $agents[$i] = Agent::updateOrCreate(['code' => $code], [
                'branch_id' => $branch->id, 'user_id' => $userId, 'name' => $name,
                'phone' => '08123456'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'email' => $email, 'commission_rate' => $rate, 'is_active' => true,
            ]);
        }

        return $agents;
    }

    /** @return array<int, Guide> */
    private function seedGuides(Branch $branch): array
    {
        $defs = [
            ['GD-DEMO1', 'Ustadz Abdullah', 'muthawif@example.com', Gender::Male, true],
            ['GD-DEMO2', 'Ustadz Fauzan', 'muthawif2@example.com', Gender::Male, false],
            ['GD-DEMO3', 'Ustadzah Khadijah', 'muthawif3@example.com', Gender::Female, false],
        ];

        $guides = [];
        foreach ($defs as $i => [$code, $name, $email, $gender, $withAccount]) {
            $userId = null;
            if ($withAccount) {
                $user = User::updateOrCreate(['email' => $email], [
                    'name' => $name, 'password' => Hash::make('password'),
                    'role' => Role::Guide->value, 'branch_id' => $branch->id,
                    'is_active' => true, 'email_verified_at' => now(),
                ]);
                $userId = $user->id;
            }

            $guides[$i] = Guide::updateOrCreate(['code' => $code], [
                'branch_id' => $branch->id, 'user_id' => $userId, 'name' => $name,
                'gender' => $gender->value, 'phone' => '08987654'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'email' => $email, 'specialization' => 'Fiqih Manasik', 'is_active' => true,
            ]);
        }

        return $guides;
    }

    /** @return array<int, Pilgrim> */
    private function seedPilgrims(Branch $branch, array $agents): array
    {
        $names = [
            'Ahmad Fauzi', 'Budi Santoso', 'Citra Lestari', 'Dewi Anggraini', 'Eko Prasetyo',
            'Fitri Handayani', 'Gunawan Wibowo', 'Hesti Purnama', 'Indra Kusuma', 'Joko Widodo',
            'Kartika Sari', 'Lukman Hakim',
        ];

        // First pilgrim is the demo account.
        $pilgrims = [];
        foreach ($names as $i => $name) {
            $isDemo = $i === 0;
            $email = $isDemo ? 'jamaah@example.com' : 'jamaah'.($i + 1).'@example.com';

            $user = User::updateOrCreate(['email' => $email], [
                'name' => $name, 'password' => Hash::make('password'),
                'role' => Role::Pilgrim->value, 'branch_id' => $branch->id,
                'is_active' => true, 'email_verified_at' => now(),
            ]);

            $agent = $agents[$i % count($agents)];
            $gender = $i % 3 === 1 ? Gender::Female : Gender::Male;

            $pilgrims[$i] = Pilgrim::updateOrCreate(['code' => 'JM-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT)], [
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'agent_id' => $i % 4 === 0 ? null : $agent->id, // some self-registered
                'name' => $name,
                'gender' => $gender->value,
                'nik' => '32010'.str_pad((string) ($i + 1), 11, '0', STR_PAD_LEFT),
                'birth_place' => 'Jakarta',
                'birth_date' => Carbon::create(1975 + $i, ($i % 12) + 1, ($i % 27) + 1),
                'address' => 'Jl. Contoh No. '.($i + 1).', Jakarta',
                'city' => 'Jakarta',
                'phone' => '0812000'.str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
                'email' => $email,
                'passport_number' => 'A'.str_pad((string) (1000000 + $i), 7, '0', STR_PAD_LEFT),
                'passport_issued_at' => Carbon::today()->subYears(2),
                'passport_expired_at' => Carbon::today()->addYears(3)->subMonths($i % 6),
                'passport_issued_place' => 'Jakarta',
                'mahram_name' => $gender === Gender::Female ? 'Suami '.$name : null,
                'mahram_relation' => $gender === Gender::Female ? 'Suami' : null,
                'emergency_contact_name' => 'Keluarga '.$name,
                'emergency_contact_phone' => '0813000'.str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
                'blood_type' => ['A', 'B', 'AB', 'O'][$i % 4],
                'is_active' => true,
            ]);
        }

        return $pilgrims;
    }

    private function seedRegistrationsAndMoney(array $packages, array $pilgrims, array $agents, array $guides): void
    {
        $docService = app(DocumentService::class);

        foreach ($pilgrims as $i => $pilgrim) {
            $package = $packages[$i % count($packages)];
            $guide = $guides[$i % count($guides)];

            // Spread registrations across the last 12 months for the line chart.
            $createdAt = Carbon::now()->subMonths(11 - ($i % 12))->addDays(($i * 2) % 25);

            $registration = Registration::updateOrCreate(
                ['pilgrim_id' => $pilgrim->id, 'package_id' => $package->id],
                [
                    'branch_id' => $package->branch_id,
                    'agent_id' => $pilgrim->agent_id,
                    'guide_id' => $guide->id,
                    'code' => 'REG-'.str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
                    'status' => RegistrationStatus::Confirmed->value,
                    'total_price' => $package->price,
                    'registered_at' => $createdAt->toDateString(),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]
            );

            $this->seedPaymentsFor($registration, $package, $i, $createdAt);
            $this->seedCommissionFor($registration, $pilgrim, $createdAt);
            $this->seedEquipmentFor($registration, $i);
            $this->seedManifestFor($registration, $package, $i);

            // Documents: seed checklist, mark some uploaded/verified.
            $docService->seedChecklist($pilgrim);
            $this->markDocuments($pilgrim, $i);
        }
    }

    private function seedEquipmentFor(Registration $registration, int $i): void
    {
        $items = [
            ['Koper', $i % 2 === 0 ? '24 inch' : '28 inch', EquipmentStatus::HandedOver],
            ['Seragam', ['S', 'M', 'L', 'XL'][$i % 4], $i % 3 === 0 ? EquipmentStatus::HandedOver : EquipmentStatus::Prepared],
            ['Kain Ihram', '-', EquipmentStatus::Pending],
        ];

        foreach ($items as $idx => [$item, $size, $status]) {
            EquipmentDistribution::updateOrCreate(
                ['registration_id' => $registration->id, 'item' => $item],
                [
                    'size' => $size,
                    'quantity' => 1,
                    'status' => $status->value,
                    'handed_at' => $status === EquipmentStatus::HandedOver ? today()->subDays($idx) : null,
                ]
            );
        }
    }

    private function seedManifestFor(Registration $registration, Package $package, int $i): void
    {
        ManifestEntry::updateOrCreate(
            ['package_id' => $package->id, 'registration_id' => $registration->id],
            [
                'room_number' => (string) (100 + intdiv($i, 4)),
                'room_type' => ['quad', 'triple', 'double'][$i % 3],
                'bus_number' => 'BUS-'.(($i % 2) + 1),
                'seat_number' => (string) ($i + 1),
                'mahram_group' => 'GRUP-'.chr(65 + ($i % 4)),
            ]
        );
    }

    private function seedPaymentsFor(Registration $registration, Package $package, int $i, Carbon $createdAt): void
    {
        $mode = $i % 3; // 0 = lunas, 1 = sebagian, 2 = belum
        $total = (float) $package->price;
        $dp = (float) $package->down_payment;
        $installmentTotal = max(0, $total - $dp);
        $installmentAmount = round($installmentTotal / 3, 2);

        // DP bill
        $dpPaid = $mode !== 2 ? $dp : 0.0;
        $this->upsertPayment($registration, 'DP', PaymentType::DownPayment, $dp, $dpPaid, $createdAt->copy()->addDays(2));

        // 3 installment bills
        for ($n = 1; $n <= 3; $n++) {
            $amount = $n === 3 ? round($installmentTotal - 2 * $installmentAmount, 2) : $installmentAmount;
            $paid = match ($mode) {
                0 => $amount,                                  // lunas
                1 => $n === 1 ? $amount : 0.0,                 // sebagian (cicilan 1 saja)
                default => 0.0,                                // belum
            };
            $this->upsertPayment($registration, 'C'.$n, PaymentType::Installment, $amount, $paid, $createdAt->copy()->addMonths($n));
        }
    }

    private function upsertPayment(Registration $registration, string $suffix, PaymentType $type, float $amount, float $paid, Carbon $due): void
    {
        $status = match (true) {
            $paid <= 0 => PaymentStatus::Unpaid,
            $paid >= $amount => PaymentStatus::Paid,
            default => PaymentStatus::Partial,
        };

        Payment::updateOrCreate(
            ['code' => $registration->code.'-'.$suffix],
            [
                'branch_id' => $registration->branch_id,
                'registration_id' => $registration->id,
                'type' => $type->value,
                'amount' => $amount,
                'paid_amount' => $paid,
                'status' => $status->value,
                'due_date' => $due->toDateString(),
                'paid_at' => $paid > 0 ? $due->copy()->subDays(3)->toDateString() : null,
                'method' => $paid > 0 ? 'transfer' : null,
            ]
        );
    }

    private function seedCommissionFor(Registration $registration, Pilgrim $pilgrim, Carbon $createdAt): void
    {
        if (! $registration->agent_id) {
            return;
        }

        $agent = Agent::find($registration->agent_id);
        $rate = (float) ($agent?->commission_rate ?? 0);
        $amount = round((float) $registration->total_price * $rate / 100, 2);
        $status = [CommissionStatus::Pending, CommissionStatus::Approved, CommissionStatus::Paid][$registration->id % 3];

        Commission::updateOrCreate(
            ['registration_id' => $registration->id],
            [
                'branch_id' => $registration->branch_id,
                'agent_id' => $registration->agent_id,
                'base_amount' => $registration->total_price,
                'rate' => $rate,
                'amount' => $amount,
                'status' => $status->value,
                'paid_at' => $status === CommissionStatus::Paid ? $createdAt->copy()->addMonths(2)->toDateString() : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]
        );
    }

    private function markDocuments(Pilgrim $pilgrim, int $i): void
    {
        $docs = $pilgrim->documents()->orderBy('id')->get();
        foreach ($docs as $idx => $doc) {
            // Vary statuses so the pilgrim dashboard checklist is colourful.
            $status = match (($i + $idx) % 4) {
                0 => DocumentStatus::Verified,
                1 => DocumentStatus::Uploaded,
                2 => DocumentStatus::Pending,
                default => DocumentStatus::Verified,
            };
            $doc->update([
                'status' => $status->value,
                'uploaded_at' => $status !== DocumentStatus::Pending ? now() : null,
                'verified_at' => $status === DocumentStatus::Verified ? now() : null,
            ]);
        }
    }

    private function seedManasik(array $packages, array $guides): void
    {
        foreach ($packages as $i => $package) {
            $guide = $guides[$i % count($guides)];

            // One past (with attendance) + one upcoming schedule per package.
            $schedules = [
                ['Manasik '.$package->name.' #1', Carbon::today()->subDays(20 - $i), true],
                ['Manasik '.$package->name.' #2', Carbon::today()->addDays(10 + $i), false],
            ];

            foreach ($schedules as $s => [$title, $date, $withAttendance]) {
                $schedule = ManasikSchedule::updateOrCreate(
                    ['title' => $title],
                    [
                        'branch_id' => $package->branch_id,
                        'package_id' => $package->id,
                        'guide_id' => $guide->id,
                        'date' => $date->toDateString(),
                        'start_time' => '08:00',
                        'end_time' => '12:00',
                        'location' => 'Aula '.$package->branch?->name,
                        'description' => 'Pembekalan manasik dan tata cara ibadah.',
                    ]
                );

                if (! $withAttendance) {
                    continue;
                }

                $registrations = $package->registrations()->get();
                foreach ($registrations as $idx => $reg) {
                    $status = [AttendanceStatus::Present, AttendanceStatus::Present, AttendanceStatus::Absent, AttendanceStatus::Excused][$idx % 4];
                    ManasikAttendance::updateOrCreate(
                        ['manasik_schedule_id' => $schedule->id, 'pilgrim_id' => $reg->pilgrim_id],
                        ['status' => $status->value]
                    );
                }
            }
        }
    }

    private function seedAnnouncements(Branch $branch): void
    {
        $defs = [
            ['Pelunasan Umroh Reguler', 'Mohon segera melunasi biaya paket Umroh Reguler sebelum H-30 keberangkatan.', AnnouncementAudience::Pilgrims],
            ['Jadwal Manasik Akbar', 'Manasik akbar akan dilaksanakan akhir bulan ini di aula utama. Wajib hadir.', AnnouncementAudience::All],
            ['Target Komisi Bulanan', 'Selamat kepada para agen! Mari capai target referral bulan ini.', AnnouncementAudience::Agents],
        ];

        foreach ($defs as $i => [$title, $body, $audience]) {
            Announcement::updateOrCreate(['title' => $title], [
                'branch_id' => $i === 1 ? null : $branch->id, // one global
                'created_by' => User::where('role', Role::SuperAdmin->value)->value('id'),
                'body' => $body,
                'audience' => $audience->value,
                'is_published' => true,
                'publish_at' => now()->subDays($i + 1),
            ]);
        }
    }
}
