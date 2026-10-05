<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Branch;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\SmokeTestCase;

/**
 * Mengaudit fitur baru: guard halaman login, CRUD + reset password pengguna,
 * serta email reset password yang sudah dibranding.
 */
class UserManagementAuthTest extends SmokeTestCase
{
    use RefreshDatabase;

    private function branch(): Branch
    {
        return Branch::firstOrCreate(['code' => 'PST'], ['name' => 'Pusat', 'is_active' => true]);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => Role::SuperAdmin,
            'is_active' => true,
            'branch_id' => $this->branch()->id,
        ]);
    }

    public function test_guest_sees_login_but_authenticated_is_redirected(): void
    {
        $this->get('/login')->assertOk();
        $this->actingAs($this->admin())->get('/login')->assertRedirect('/dashboard');
    }

    public function test_admin_can_create_user_with_role(): void
    {
        $this->actingAs($this->admin())->post('/users', [
            'name' => 'Operator Cabang',
            'email' => 'operator@example.com',
            'role' => Role::Admin->value,
            'branch_id' => $this->branch()->id,
            'is_active' => 1,
            'password' => 'rahasia123',
        ])->assertRedirect('/users');

        $this->assertDatabaseHas('users', [
            'email' => 'operator@example.com',
            'role' => Role::Admin->value,
        ]);
    }

    public function test_admin_can_reset_user_password(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create([
            'role' => Role::Agent,
            'password' => Hash::make('old-password'),
            'branch_id' => $this->branch()->id,
        ]);
        $oldHash = $target->password;

        $response = $this->actingAs($admin)->post("/users/{$target->id}/reset-password");

        $response->assertRedirect('/users');
        $response->assertSessionHas('generated_password');
        $this->assertNotSame($oldHash, $target->fresh()->password, 'Password hash harus berubah.');
    }

    public function test_non_staff_cannot_reset_password(): void
    {
        $agent = User::factory()->create(['role' => Role::Agent, 'branch_id' => $this->branch()->id]);
        $target = User::factory()->create(['role' => Role::Pilgrim, 'branch_id' => $this->branch()->id]);

        $this->actingAs($agent)->post("/users/{$target->id}/reset-password")->assertForbidden();
    }

    public function test_password_reset_email_is_branded(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'lupa@example.com', 'is_active' => true]);

        Password::sendResetLink(['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $mail = $notification->toMail($user);
            $rendered = $mail->salutation;

            return str_contains((string) $rendered, 'Toko Source Code')
                && str_contains((string) $rendered, 'id.shp.ee/gteU4cYe');
        });
    }
}
