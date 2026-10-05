<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Airline;
use App\Models\Announcement;
use App\Models\BankAccount;
use App\Models\Branch;
use App\Models\Commission;
use App\Models\Document;
use App\Models\EquipmentDistribution;
use App\Models\Guide;
use App\Models\Hotel;
use App\Models\ManasikSchedule;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Pilgrim;
use App\Models\Registration;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as IlluminateRoute;
use Illuminate\Support\Facades\Route;
use Tests\SmokeTestCase;

/**
 * Audit menyeluruh: meng-crawl SETIAP route GET dan benar-benar membuka
 * halamannya untuk SETIAP role (super_admin, admin, agent, pilgrim, guide).
 *
 * Tidak boleh ada halaman yang crash (status 5xx / exception). Status 403
 * (ditolak otorisasi) dan 404 dianggap WAJAR, bukan crash — itu bukti RBAC
 * bekerja. Hasil lengkap ditulis ke storage/logs/page-audit.txt.
 */
class FullRouteAuditTest extends SmokeTestCase
{
    use RefreshDatabase;

    /** Route util/auth yang tidak relevan diaudit sebagai "halaman". */
    private const SKIP_NAMES = [
        'login', 'logout', 'register', 'home',
        'password.request', 'password.email', 'password.reset', 'password.store',
        'password.confirm', 'password.update',
        'verification.notice', 'verification.verify', 'verification.send',
        'storage.local', 'storage.local.upload',
    ];

    private const ROLE_ACCOUNTS = [
        'super_admin' => 'superadmin@example.com',
        'admin' => 'admin@example.com',
        'agent' => 'agent@example.com',
        'pilgrim' => 'jamaah@example.com',
        'guide' => 'muthawif@example.com',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoDataSeeder::class);
    }

    /** Nilai contoh untuk tiap nama parameter route, diambil dari data ter-seed. */
    private function paramMap(): array
    {
        return array_filter([
            'branch' => Branch::value('id'),
            'hotel' => Hotel::value('id'),
            'airline' => Airline::value('id'),
            'bank_account' => BankAccount::value('id'),
            'package' => Package::value('id'),
            'agent' => Agent::value('id'),
            'guide' => Guide::value('id'),
            'pilgrim' => Pilgrim::value('id'),
            'registration' => Registration::value('id'),
            'payment' => Payment::value('id'),
            'document' => Document::value('id'),
            'manasik' => ManasikSchedule::value('id'),
            'commission' => Commission::value('id'),
            'announcement' => Announcement::value('id'),
            'equipment' => EquipmentDistribution::value('id'),
            'user' => User::value('id'),
        ], fn ($v) => $v !== null);
    }

    /** @return array{0:?string,1:bool} URL atau [null,false] bila param tak terpenuhi */
    private function buildUrl(IlluminateRoute $route, array $params): array
    {
        $uri = $route->uri();

        foreach ($route->parameterNames() as $name) {
            if (! array_key_exists($name, $params)) {
                return [null, false];
            }
            $uri = preg_replace('/\{'.$name.'\??\}/', (string) $params[$name], $uri);
        }

        return ['/'.ltrim($uri, '/'), true];
    }

    public function test_no_role_crashes_on_any_get_page(): void
    {
        $params = $this->paramMap();

        $getRoutes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn (IlluminateRoute $r) => in_array('GET', $r->methods(), true))
            ->filter(fn (IlluminateRoute $r) => ! in_array($r->getName(), self::SKIP_NAMES, true))
            ->filter(fn (IlluminateRoute $r) => ! str_starts_with($r->uri(), 'storage/') && $r->uri() !== 'up' && $r->uri() !== '_ignition')
            ->values();

        $failures = [];
        $lines = ['=== AUDIT HALAMAN PER ROLE (GET) ===', 'Total route GET diaudit: '.$getRoutes->count(), ''];

        foreach (self::ROLE_ACCOUNTS as $role => $email) {
            $user = User::where('email', $email)->firstOrFail();
            $c = ['ok' => 0, 'redirect' => 0, 'forbidden' => 0, 'notfound' => 0, 'skip' => 0, 'error' => 0];

            foreach ($getRoutes as $route) {
                [$url, $ok] = $this->buildUrl($route, $params);
                if (! $ok) {
                    $c['skip']++;

                    continue;
                }

                try {
                    $status = $this->actingAs($user)->get($url)->status();
                } catch (\Throwable $e) {
                    $c['error']++;
                    $failures[] = "[$role] GET $url  ⇒ EXCEPTION: ".$e->getMessage().' @ '.basename($e->getFile()).':'.$e->getLine();

                    continue;
                }

                match (true) {
                    $status >= 500 => [$c['error']++, $failures[] = "[$role] GET $url  ⇒ HTTP $status"],
                    $status === 403 => $c['forbidden']++,
                    $status === 404 => $c['notfound']++,
                    $status >= 300 => $c['redirect']++,
                    default => $c['ok']++,
                };
            }

            $lines[] = sprintf(
                '%-12s  ok:%-3d redirect:%-3d ditolak(403):%-3d notfound(404):%-3d dilewati:%-3d  CRASH(5xx):%d',
                $role, $c['ok'], $c['redirect'], $c['forbidden'], $c['notfound'], $c['skip'], $c['error']
            );
        }

        $lines[] = '';
        $lines[] = $failures === []
            ? '✅ TIDAK ADA HALAMAN YANG CRASH untuk role mana pun.'
            : '❌ DITEMUKAN '.count($failures).' HALAMAN CRASH:';
        $lines = array_merge($lines, $failures);

        @mkdir(storage_path('logs'), 0775, true);
        file_put_contents(storage_path('logs/page-audit.txt'), implode("\n", $lines)."\n");

        $this->assertSame([], $failures, "Ada halaman yang crash:\n".implode("\n", $failures));
    }
}
