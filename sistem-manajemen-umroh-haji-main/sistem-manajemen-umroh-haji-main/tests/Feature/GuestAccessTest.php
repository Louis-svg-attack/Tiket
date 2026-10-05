<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\SmokeTestCase;

/**
 * Halaman tamu (login, lupa password) hanya untuk yang BELUM login. Pengguna
 * yang sudah login diarahkan ke dashboard, tak bisa membuka halaman login lagi
 * kecuali setelah logout.
 */
class GuestAccessTest extends SmokeTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoDataSeeder::class);
    }

    public function test_guest_can_open_login_page(): void
    {
        $this->get('/login')->assertOk()->assertSee('Password', false);
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $user = User::where('email', 'superadmin@example.com')->firstOrFail();

        $this->actingAs($user)->get('/login')->assertRedirect('/dashboard');
        $this->actingAs($user)->get('/forgot-password')->assertRedirect('/dashboard');
    }

    public function test_after_logout_login_page_is_reachable_again(): void
    {
        $user = User::where('email', 'superadmin@example.com')->firstOrFail();

        // Logged in → blocked.
        $this->actingAs($user)->get('/login')->assertRedirect('/dashboard');

        // After logout → reachable again.
        $this->post('/logout');
        $this->get('/login')->assertOk();
    }
}
