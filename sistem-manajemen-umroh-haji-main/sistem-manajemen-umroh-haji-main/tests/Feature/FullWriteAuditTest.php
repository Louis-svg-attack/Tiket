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
 * Audit menyeluruh untuk jalur TULIS: meng-crawl SETIAP route non-GET
 * (POST/PUT/PATCH/DELETE) dan menembaknya dengan payload KOSONG untuk SETIAP
 * role. Tujuannya bukan agar aksi berhasil, tapi membuktikan tidak ada
 * controller yang CRASH (5xx / exception) saat menerima input invalid, tak
 * lengkap, atau dari role yang tak berwenang.
 *
 * Status yang WAJAR (bukan crash): 419 (CSRF), 422/302 (validasi gagal),
 * 403 (ditolak Policy), 404 (resource tak ada), 405 (method). Hanya 5xx /
 * exception yang dianggap kegagalan. Hasil lengkap → storage/logs/write-audit.txt
 */
class FullWriteAuditTest extends SmokeTestCase
{
    use RefreshDatabase;

    private const SKIP_NAMES = [
        'login', 'logout', 'register',
        'password.request', 'password.email', 'password.reset', 'password.store',
        'password.confirm', 'password.update',
        'verification.notice', 'verification.verify', 'verification.send',
        'storage.local', 'storage.local.upload',
        // Mengubah konteks sesi/akun sendiri — bukan operasi data domain.
        'profile.destroy',
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

    /** @return array{0:?string,1:bool} */
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

    private function firstWriteMethod(IlluminateRoute $route): string
    {
        foreach (['POST', 'PUT', 'PATCH', 'DELETE'] as $m) {
            if (in_array($m, $route->methods(), true)) {
                return $m;
            }
        }

        return 'POST';
    }

    public function test_no_role_crashes_on_any_write_route_with_empty_payload(): void
    {
        $params = $this->paramMap();

        $writeRoutes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn (IlluminateRoute $r) => (bool) array_intersect(['POST', 'PUT', 'PATCH', 'DELETE'], $r->methods()))
            ->filter(fn (IlluminateRoute $r) => ! in_array($r->getName(), self::SKIP_NAMES, true))
            ->filter(fn (IlluminateRoute $r) => ! str_starts_with($r->uri(), 'storage/'))
            ->values();

        $failures = [];
        $lines = ['=== AUDIT JALUR TULIS PER ROLE (POST/PUT/PATCH/DELETE, payload kosong) ===',
            'Total route tulis diaudit: '.$writeRoutes->count(), ''];

        foreach (self::ROLE_ACCOUNTS as $role => $email) {
            $user = User::where('email', $email)->firstOrFail();
            $c = ['handled' => 0, 'forbidden' => 0, 'notfound' => 0, 'skip' => 0, 'error' => 0];

            foreach ($writeRoutes as $route) {
                [$url, $ok] = $this->buildUrl($route, $params);
                if (! $ok) {
                    $c['skip']++;

                    continue;
                }

                $method = $this->firstWriteMethod($route);

                try {
                    $status = $this->actingAs($user)
                        ->call($method, $url, [], [], [], ['HTTP_ACCEPT' => 'text/html'])
                        ->status();
                } catch (\Throwable $e) {
                    $c['error']++;
                    $failures[] = "[$role] $method $url  ⇒ EXCEPTION: ".$e->getMessage().' @ '.basename($e->getFile()).':'.$e->getLine();

                    continue;
                }

                match (true) {
                    $status >= 500 => [$c['error']++, $failures[] = "[$role] $method $url  ⇒ HTTP $status"],
                    $status === 403 => $c['forbidden']++,
                    $status === 404 => $c['notfound']++,
                    default => $c['handled']++,
                };
            }

            $lines[] = sprintf(
                '%-12s  handled:%-3d ditolak(403):%-3d notfound(404):%-3d dilewati:%-3d  CRASH(5xx):%d',
                $role, $c['handled'], $c['forbidden'], $c['notfound'], $c['skip'], $c['error']
            );
        }

        $lines[] = '';
        $lines[] = $failures === []
            ? '✅ TIDAK ADA JALUR TULIS YANG CRASH untuk role mana pun.'
            : '❌ DITEMUKAN '.count($failures).' JALUR TULIS CRASH:';
        $lines = array_merge($lines, $failures);

        @mkdir(storage_path('logs'), 0775, true);
        file_put_contents(storage_path('logs/write-audit.txt'), implode("\n", $lines)."\n");

        $this->assertSame([], $failures, "Ada jalur tulis yang crash:\n".implode("\n", $failures));
    }
}
