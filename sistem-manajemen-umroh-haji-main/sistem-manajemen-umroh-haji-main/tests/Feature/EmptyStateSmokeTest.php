<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\SmokeTestCase;

/**
 * Hardest case: ZERO domain data, role accounts with NO linked profile, and a
 * brand-new empty branch. Nothing may 500 — empty states must show instead.
 */
class EmptyStateSmokeTest extends SmokeTestCase
{
    use RefreshDatabase;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        // A fresh, empty branch — no packages, pilgrims, anything.
        $this->branch = Branch::create([
            'code' => 'NEW', 'name' => 'Cabang Baru Kosong', 'is_active' => true,
        ]);
    }

    private function makeUser(Role $role): User
    {
        return User::create([
            'name' => 'Tanpa Profil '.$role->value,
            'email' => $role->value.'.noprofile@test.local',
            'password' => Hash::make('password'),
            'role' => $role->value,
            'branch_id' => $this->branch->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    private function assertNoServerError(User $user, array $urls): void
    {
        foreach ($urls as $url) {
            $response = $this->actingAs($user)->get($url);
            $this->assertLessThan(
                500,
                $response->status(),
                "Server error on EMPTY {$url} for {$user->role->value} (status {$response->status()})"
            );
        }
    }

    public function test_staff_empty_branch_renders(): void
    {
        $user = $this->makeUser(Role::SuperAdmin);

        $this->assertNoServerError($user, [
            '/dashboard', '/branches', '/hotels', '/airlines', '/bank-accounts',
            '/packages', '/agents', '/guides', '/pilgrims', '/registrations',
            '/documents', '/payments', '/payments/generate-massal', '/manasik',
            '/manifests', '/equipment', '/commissions', '/announcements',
            '/users', '/activity-logs', '/settings',
            '/hotels/create', '/packages/create', '/agents/create',
            '/guides/create', '/pilgrims/create', '/registrations/create',
            '/manasik/create', '/announcements/create', '/users/create',
        ]);
    }

    public function test_admin_empty_branch_renders(): void
    {
        $user = $this->makeUser(Role::Admin);

        $this->assertNoServerError($user, [
            '/dashboard', '/hotels', '/packages', '/agents', '/guides',
            '/pilgrims', '/registrations', '/documents', '/payments',
            '/manasik', '/manifests', '/equipment', '/commissions', '/announcements',
        ]);
    }

    public function test_agent_without_profile_renders(): void
    {
        // Agent account whose Agent row does not exist.
        $user = $this->makeUser(Role::Agent);

        $this->assertNoServerError($user, [
            '/dashboard', '/pilgrims', '/registrations', '/registrations/create',
            '/packages', '/payments', '/commissions', '/manasik',
        ]);
    }

    public function test_pilgrim_without_profile_renders(): void
    {
        $user = $this->makeUser(Role::Pilgrim);

        $this->assertNoServerError($user, [
            '/dashboard', '/registrations', '/documents', '/payments', '/manasik',
        ]);
    }

    public function test_guide_without_profile_renders(): void
    {
        $user = $this->makeUser(Role::Guide);

        $this->assertNoServerError($user, [
            '/dashboard', '/pilgrims', '/manasik',
        ]);
    }
}
