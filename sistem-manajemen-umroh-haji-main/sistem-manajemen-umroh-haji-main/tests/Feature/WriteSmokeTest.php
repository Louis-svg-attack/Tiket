<?php

namespace Tests\Feature;

use App\Enums\PackageStatus;
use App\Enums\PackageType;
use App\Enums\PaymentStatus;
use App\Models\Agent;
use App\Models\Branch;
use App\Models\ManasikSchedule;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Pilgrim;
use App\Models\Registration;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\SmokeTestCase;

/**
 * Exercises the WRITE paths (create/store, booking with quota lock, payment
 * recording, attendance, account provisioning). RefreshDatabase wraps every
 * test in a transaction that rolls back, so the database is never polluted.
 */
class WriteSmokeTest extends SmokeTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoDataSeeder::class);
    }

    private function staff(): User
    {
        return User::where('email', 'superadmin@example.com')->firstOrFail();
    }

    public function test_create_hotel(): void
    {
        $response = $this->actingAs($this->staff())->post('/hotels', [
            'name' => 'Hotel Uji Coba', 'city' => 'Mekah', 'star' => 5,
            'distance_to_masjid' => 120, 'is_active' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('hotels', ['name' => 'Hotel Uji Coba', 'star' => 5]);
    }

    public function test_create_package(): void
    {
        $branch = Branch::first();

        $response = $this->actingAs($this->staff())->post('/packages', [
            'branch_id' => $branch->id,
            'name' => 'Paket Uji Coba',
            'type' => PackageType::Umroh->value,
            'price' => 30000000,
            'down_payment' => 5000000,
            'quota' => 10,
            'duration_days' => 9,
            'status' => PackageStatus::Open->value,
            'facilities_included' => "Tiket\nHotel\nVisa",
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('packages', ['name' => 'Paket Uji Coba']);

        $package = Package::where('name', 'Paket Uji Coba')->first();
        $this->assertIsArray($package->facilities_included);
        $this->assertContains('Tiket', $package->facilities_included);
    }

    public function test_booking_creates_registration_and_respects_quota(): void
    {
        // A package with a known small remaining quota.
        $package = Package::create([
            'branch_id' => Branch::first()->id,
            'name' => 'Paket Kuota Satu', 'code' => 'PKG-Q1',
            'type' => PackageType::Umroh->value, 'price' => 25000000,
            'down_payment' => 5000000, 'quota' => 1, 'duration_days' => 9,
            'status' => PackageStatus::Open->value,
        ]);

        $pilgrimA = Pilgrim::first();
        $pilgrimB = Pilgrim::skip(1)->first();

        // First booking succeeds.
        $this->actingAs($this->staff())->post('/registrations', [
            'pilgrim_id' => $pilgrimA->id,
            'package_id' => $package->id,
            'registered_at' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('registrations', [
            'pilgrim_id' => $pilgrimA->id, 'package_id' => $package->id,
        ]);
        $this->assertSame(0, $package->fresh()->remainingQuota());

        // Second booking must be rejected gracefully (no 500), quota full.
        $response = $this->actingAs($this->staff())->post('/registrations', [
            'pilgrim_id' => $pilgrimB->id,
            'package_id' => $package->id,
            'registered_at' => now()->toDateString(),
        ]);
        $response->assertRedirect();
        $this->assertDatabaseMissing('registrations', [
            'pilgrim_id' => $pilgrimB->id, 'package_id' => $package->id,
        ]);
    }

    public function test_generate_bills_and_record_payment(): void
    {
        $registration = Registration::doesntHave('payments')->first()
            ?? Registration::first();

        // Remove any existing bills so generation is clean.
        $registration->payments()->forceDelete();

        $this->actingAs($this->staff())
            ->post("/payments/registration/{$registration->id}/generate", [
                'installments' => 3,
                'dp_amount' => 5000000,
                'first_due' => now()->toDateString(),
            ])->assertRedirect();

        $this->assertGreaterThan(0, $registration->fresh()->payments()->count());

        $bill = $registration->fresh()->payments()->first();
        $this->actingAs($this->staff())->post("/payments/{$bill->id}/record", [
            'amount' => (float) $bill->amount,
            'method' => 'transfer',
            'paid_at' => now()->toDateString(),
        ])->assertRedirect();

        $bill->refresh();
        $this->assertEquals(PaymentStatus::Paid, $bill->status);
        $this->assertEquals((float) $bill->amount, (float) $bill->paid_amount);
    }

    public function test_record_attendance(): void
    {
        $schedule = ManasikSchedule::has('package')->first();
        $pilgrim = $schedule->package->registrations()->first()?->pilgrim
            ?? Pilgrim::first();

        $this->actingAs($this->staff())
            ->post("/manasik/{$schedule->id}/attendance", [
                'status' => [$pilgrim->id => 'present'],
            ])->assertRedirect();

        $this->assertDatabaseHas('manasik_attendances', [
            'manasik_schedule_id' => $schedule->id,
            'pilgrim_id' => $pilgrim->id,
            'status' => 'present',
        ]);
    }

    public function test_create_agent_with_login_account(): void
    {
        $branch = Branch::first();

        $response = $this->actingAs($this->staff())->post('/agents', [
            'branch_id' => $branch->id,
            'name' => 'Agen Baru Uji',
            'email' => 'agen.baru.uji@test.local',
            'commission_rate' => 5,
            'is_active' => 1,
            'create_account' => 1,
            'account_email' => 'agen.baru.uji@test.local',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('agents', ['name' => 'Agen Baru Uji']);

        $agent = Agent::where('name', 'Agen Baru Uji')->first();
        $this->assertNotNull($agent->user_id, 'Login account should be linked');
        $this->assertDatabaseHas('users', [
            'id' => $agent->user_id, 'role' => 'agent',
        ]);
    }

    public function test_create_guide_with_login_account(): void
    {
        $branch = \App\Models\Branch::first();

        $this->actingAs($this->staff())->post('/guides', [
            'branch_id' => $branch->id,
            'name' => 'Muthawif Baru Uji',
            'gender' => 'male',
            'email' => 'muthawif.baru.uji@test.local',
            'is_active' => 1,
            'create_account' => 1,
            'account_email' => 'muthawif.baru.uji@test.local',
        ])->assertRedirect();

        $guide = \App\Models\Guide::where('name', 'Muthawif Baru Uji')->first();
        $this->assertNotNull($guide->user_id, 'Akun login muthawif harus tertaut');
        $this->assertDatabaseHas('users', ['id' => $guide->user_id, 'role' => 'guide']);
    }

    public function test_create_pilgrim_with_login_account(): void
    {
        $branch = \App\Models\Branch::first();

        $this->actingAs($this->staff())->post('/pilgrims', [
            'branch_id' => $branch->id,
            'name' => 'Jamaah Baru Uji',
            'gender' => 'female',
            'phone' => '081200099999',
            'email' => 'jamaah.baru.uji@test.local',
            'is_active' => 1,
            'create_account' => 1,
            'account_email' => 'jamaah.baru.uji@test.local',
        ])->assertRedirect();

        $pilgrim = Pilgrim::where('name', 'Jamaah Baru Uji')->first();
        $this->assertNotNull($pilgrim->user_id, 'Akun login jamaah harus tertaut');
        $this->assertDatabaseHas('users', ['id' => $pilgrim->user_id, 'role' => 'pilgrim']);
    }

    public function test_create_user_via_users_module(): void
    {
        $branch = \App\Models\Branch::first();

        $this->actingAs($this->staff())->post('/users', [
            'name' => 'Admin Baru Uji',
            'email' => 'admin.baru.uji@test.local',
            'role' => 'admin',
            'branch_id' => $branch->id,
            'is_active' => 1,
            'password' => 'rahasia123',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'admin.baru.uji@test.local', 'role' => 'admin',
        ]);

        // Akun baru benar-benar bisa dipakai login (password ter-hash & cocok).
        $user = User::where('email', 'admin.baru.uji@test.local')->first();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('rahasia123', $user->password));
    }

    public function test_document_checklist_seed_and_verify(): void
    {
        $pilgrim = Pilgrim::first();
        $pilgrim->documents()->delete();

        $this->actingAs($this->staff())
            ->post("/documents/pilgrim/{$pilgrim->id}", ['action' => 'seed'])
            ->assertRedirect();

        $this->assertGreaterThan(0, $pilgrim->documents()->count());
    }
}
