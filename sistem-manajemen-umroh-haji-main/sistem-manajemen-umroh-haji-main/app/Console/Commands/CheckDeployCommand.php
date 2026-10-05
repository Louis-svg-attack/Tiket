<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Diagnosa penyebab umum "tidak bisa login" setelah aplikasi di-deploy.
 * Jalankan di server: php artisan app:check-deploy
 */
class CheckDeployCommand extends Command
{
    protected $signature = 'app:check-deploy';

    protected $description = 'Periksa konfigurasi server yang biasa membuat login gagal setelah deploy';

    /** @var array<int, array{0:string,1:string,2:string}> */
    private array $rows = [];

    private int $problems = 0;

    public function handle(): int
    {
        $this->checkAppKey();
        $this->checkScheme();
        $this->checkProxies();
        $this->checkDatabase();
        $this->checkTables();
        $this->checkAccounts();
        $this->checkSession();
        $this->checkCaches();
        $this->checkWritablePaths();
        $this->checkAssets();

        $this->newLine();
        $this->table(['Status', 'Pemeriksaan', 'Keterangan'], $this->rows);
        $this->newLine();

        if ($this->problems > 0) {
            $this->components->error("Ditemukan {$this->problems} masalah yang bisa membuat login gagal.");

            return self::FAILURE;
        }

        $this->components->info('Semua pemeriksaan aman. Login seharusnya berfungsi.');

        return self::SUCCESS;
    }

    private function pass(string $check, string $note): void
    {
        $this->rows[] = ['OK', $check, $note];
    }

    private function hint(string $check, string $note): void
    {
        $this->rows[] = ['PERINGATAN', $check, $note];
    }

    private function bad(string $check, string $note): void
    {
        $this->problems++;
        $this->rows[] = ['MASALAH', $check, $note];
    }

    private function checkAppKey(): void
    {
        $key = (string) config('app.key');

        $key === ''
            ? $this->bad('APP_KEY', 'Kosong. Jalankan: php artisan key:generate --force')
            : $this->pass('APP_KEY', 'Terisi (cookie sesi bisa didekripsi).');
    }

    private function checkScheme(): void
    {
        $url = (string) config('app.url');
        $force = config('app.force_https');
        $forced = $force === null
            ? str_starts_with($url, 'https://')
            : filter_var($force, FILTER_VALIDATE_BOOLEAN);

        if ($forced && ! str_starts_with($url, 'https://')) {
            $this->bad('Skema URL', "FORCE_HTTPS aktif tetapi APP_URL={$url}. Samakan keduanya atau matikan FORCE_HTTPS.");

            return;
        }

        if (! $forced && str_starts_with($url, 'https://')) {
            $this->hint('Skema URL', 'APP_URL https tetapi FORCE_HTTPS=false — bisa muncul mixed content.');

            return;
        }

        $this->pass('Skema URL', $url.($forced ? ' (HTTPS dipaksa)' : ' (HTTP apa adanya)'));

        if ($url === 'http://localhost' && app()->environment('production')) {
            $this->bad('APP_URL', 'Masih http://localhost di server produksi. Isi domain asli.');
        }
    }

    private function checkProxies(): void
    {
        config('app.trusted_proxies')
            ? $this->pass('Proxy tepercaya', 'TRUSTED_PROXIES='.config('app.trusted_proxies'))
            : $this->hint('Proxy tepercaya', 'Kosong. Bila memakai Cloudflare/nginx/load balancer, set TRUSTED_PROXIES=* agar IP pengunjung benar dan rate limit login tidak tercampur.');
    }

    private function checkDatabase(): void
    {
        try {
            DB::connection()->getPdo();
            $this->pass('Koneksi database', config('database.default').' → '.config('database.connections.'.config('database.default').'.database'));
        } catch (Throwable $e) {
            $this->bad('Koneksi database', 'Gagal: '.$e->getMessage());
        }
    }

    private function checkTables(): void
    {
        foreach (['users', 'sessions', 'cache', 'settings'] as $table) {
            try {
                Schema::hasTable($table)
                    ? $this->pass("Tabel {$table}", 'Ada.')
                    : $this->bad("Tabel {$table}", 'Tidak ada. Jalankan: php artisan migrate --force');
            } catch (Throwable $e) {
                $this->bad("Tabel {$table}", 'Tidak bisa diperiksa: '.$e->getMessage());
            }
        }
    }

    private function checkAccounts(): void
    {
        try {
            if (! Schema::hasTable('users')) {
                return;
            }

            $total = User::query()->count();
            $admins = User::query()
                ->whereIn('role', [Role::SuperAdmin->value, Role::Admin->value])
                ->where('is_active', true)
                ->count();

            if ($total === 0) {
                $this->bad('Akun pengguna', 'Tabel users kosong — tidak ada akun untuk login. Jalankan: php artisan app:create-admin');

                return;
            }

            $admins === 0
                ? $this->bad('Akun admin', "Ada {$total} user tetapi tidak ada super_admin/admin aktif. Jalankan: php artisan app:create-admin")
                : $this->pass('Akun admin', "{$admins} admin aktif dari {$total} user.");
        } catch (Throwable $e) {
            $this->bad('Akun pengguna', 'Tidak bisa diperiksa: '.$e->getMessage());
        }
    }

    private function checkSession(): void
    {
        $driver = (string) config('session.driver');
        $secure = config('session.secure');
        $url = (string) config('app.url');

        $this->pass('Driver sesi', $driver);

        if ($driver === 'database' && ! Schema::hasTable('sessions')) {
            $this->bad('Sesi database', 'Tabel sessions belum ada → sesi & token CSRF tidak tersimpan (error 419).');
        }

        if (filter_var($secure, FILTER_VALIDATE_BOOLEAN) && ! str_starts_with($url, 'https://')) {
            $this->bad('Cookie sesi', 'SESSION_SECURE_COOKIE=true tetapi situs bukan https → cookie dibuang browser, login berputar kembali ke form.');
        }

        $domain = config('session.domain');
        if ($domain && ! str_contains($url, ltrim((string) $domain, '.'))) {
            $this->bad('Cookie sesi', "SESSION_DOMAIN={$domain} tidak cocok dengan APP_URL {$url} → cookie tidak terkirim.");
        }
    }

    private function checkCaches(): void
    {
        $env = base_path('.env');
        $envTime = is_file($env) ? filemtime($env) : 0;

        foreach ([
            'Cache config' => base_path('bootstrap/cache/config.php'),
            'Cache route' => base_path('bootstrap/cache/routes-v7.php'),
        ] as $label => $path) {
            if (! is_file($path)) {
                $this->pass($label, 'Tidak di-cache (aman).');

                continue;
            }

            filemtime($path) < $envTime
                ? $this->bad($label, 'Lebih lama dari .env → konfigurasi lama masih dipakai. Jalankan: php artisan optimize:clear lalu optimize.')
                : $this->pass($label, 'Ada dan lebih baru dari .env.');
        }

        if (app()->environment('production') && config('app.debug')) {
            $this->hint('APP_DEBUG', 'true di produksi — matikan agar detail error tidak bocor.');
        }
    }

    private function checkWritablePaths(): void
    {
        foreach ([storage_path(), base_path('bootstrap/cache')] as $path) {
            is_writable($path)
                ? $this->pass('Izin tulis', basename($path).' writable.')
                : $this->bad('Izin tulis', $path.' tidak writable → sesi/log/cache gagal ditulis. chmod -R 775 & chown ke user web server.');
        }
    }

    private function checkAssets(): void
    {
        is_file(public_path('build/manifest.json'))
            ? $this->pass('Aset Vite', 'public/build/manifest.json ada.')
            : $this->bad('Aset Vite', 'Belum di-build → halaman login tampil tanpa CSS/JS. Jalankan: npm ci && npm run build');

        is_link(public_path('storage')) || is_dir(public_path('storage'))
            ? $this->pass('Symlink storage', 'public/storage tersedia (logo & foto tampil).')
            : $this->hint('Symlink storage', 'Belum ada. Jalankan: php artisan storage:link');
    }
}
