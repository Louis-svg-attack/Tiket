<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\SmokeTestCase;

class StrictStatusTest extends SmokeTestCase
{
    use RefreshDatabase;

    public function test_core_index_pages_return_200(): void
    {
        $this->seed(DemoDataSeeder::class);
        $u = User::where('email', 'superadmin@example.com')->firstOrFail();
        foreach (['/dashboard', '/branches', '/hotels', '/airlines', '/bank-accounts', '/packages', '/agents', '/guides', '/pilgrims', '/registrations', '/documents', '/payments', '/manasik', '/manifests', '/equipment', '/commissions', '/announcements', '/users', '/activity-logs', '/settings'] as $url) {
            $this->actingAs($u)->get($url)->assertOk();
        }
    }
}
