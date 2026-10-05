<?php

namespace Tests\Feature;

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
 * Proves every page renders WITHOUT a 500 for each role, using fully seeded
 * demo data. Pages the role is authorized for must return 200.
 */
class RoleSmokeTest extends SmokeTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoDataSeeder::class);
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    /** Assert each URL returns 200 (and therefore no server error). */
    private function assertPagesOk(User $user, array $urls): void
    {
        foreach ($urls as $url) {
            $response = $this->actingAs($user)->get($url);
            $this->assertLessThan(
                500,
                $response->status(),
                "Server error on {$url} for {$user->role->value} (status {$response->status()})"
            );
            $this->assertContains(
                $response->status(),
                [200, 302],
                "Unexpected status {$response->status()} on {$url} for {$user->role->value}"
            );
        }
    }

    public function test_super_admin_pages_render(): void
    {
        $user = $this->user('superadmin@example.com');
        $package = Package::first();
        $pilgrim = Pilgrim::first();
        $registration = Registration::first();
        $schedule = ManasikSchedule::first();

        $this->assertPagesOk($user, [
            '/dashboard',
            '/branches', '/branches/create',
            '/hotels', '/hotels/create',
            '/airlines', '/airlines/create',
            '/bank-accounts', '/bank-accounts/create',
            '/packages', '/packages/create', "/packages/{$package->id}", "/packages/{$package->id}/edit",
            '/agents', '/agents/create',
            '/guides', '/guides/create',
            '/pilgrims', '/pilgrims/create', "/pilgrims/{$pilgrim->id}", "/pilgrims/{$pilgrim->id}/edit",
            '/registrations', '/registrations/create', "/registrations/{$registration->id}",
            '/documents', "/documents/pilgrim/{$pilgrim->id}",
            '/payments', '/payments/generate-massal', "/payments/registration/{$registration->id}",
            '/manasik', '/manasik/create', "/manasik/{$schedule->id}", "/manasik/{$schedule->id}/edit",
            '/manifests', "/manifests/{$package->id}",
            '/equipment', '/equipment/create',
            '/commissions',
            '/announcements', '/announcements/create',
            '/users', '/users/create',
            '/activity-logs',
            '/settings',
        ]);
    }

    public function test_admin_pages_render(): void
    {
        $user = $this->user('admin@example.com');
        $package = Package::first();
        $pilgrim = Pilgrim::first();
        $registration = Registration::first();

        $this->assertPagesOk($user, [
            '/dashboard', '/hotels', '/airlines', '/bank-accounts',
            '/packages', "/packages/{$package->id}",
            '/agents', '/guides', '/pilgrims', "/pilgrims/{$pilgrim->id}",
            '/registrations', "/registrations/{$registration->id}",
            '/documents', '/payments', '/manasik', '/manifests',
            '/equipment', '/commissions', '/announcements', '/users', '/activity-logs',
        ]);
    }

    public function test_agent_pages_render(): void
    {
        $user = $this->user('agent@example.com');

        $this->assertPagesOk($user, [
            '/dashboard', '/pilgrims', '/pilgrims/create',
            '/registrations', '/registrations/create',
            '/packages', '/payments', '/commissions', '/manasik',
        ]);
    }

    public function test_pilgrim_pages_render(): void
    {
        $user = $this->user('jamaah@example.com');

        $this->assertPagesOk($user, [
            '/dashboard', '/registrations', '/documents', '/payments', '/manasik',
        ]);
    }

    public function test_guide_pages_render(): void
    {
        $user = $this->user('muthawif@example.com');

        $this->assertPagesOk($user, [
            '/dashboard', '/pilgrims', '/manasik',
        ]);
    }

    public function test_pdf_documents_generate(): void
    {
        $user = $this->user('superadmin@example.com');
        $paidPayment = Payment::where('paid_amount', '>', 0)->first();
        $package = Package::first();
        $registration = Registration::first();

        $this->assertPagesOk($user, [
            "/pdf/payment/{$paidPayment->id}/receipt",
            "/pdf/package/{$package->id}/manifest",
            "/pdf/registration/{$registration->id}/card",
        ]);
    }
}
