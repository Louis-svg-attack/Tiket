<?php

namespace App\Providers;

use App\Models;
use App\Policies;
use App\Services\AccessContext;
use App\Services\ActivityLogService;
use App\Services\BranchContext;
use App\Services\FileVault;
use App\Services\SettingService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** Model => Policy map (registered explicitly per requirement 9.4). */
    public const POLICIES = [
        Models\Branch::class => Policies\BranchPolicy::class,
        Models\Package::class => Policies\PackagePolicy::class,
        Models\Hotel::class => Policies\MasterDataPolicy::class,
        Models\Airline::class => Policies\MasterDataPolicy::class,
        Models\BankAccount::class => Policies\MasterDataPolicy::class,
        Models\Agent::class => Policies\AgentPolicy::class,
        Models\Guide::class => Policies\GuidePolicy::class,
        Models\Pilgrim::class => Policies\PilgrimPolicy::class,
        Models\Registration::class => Policies\RegistrationPolicy::class,
        Models\Document::class => Policies\DocumentPolicy::class,
        Models\Payment::class => Policies\PaymentPolicy::class,
        Models\ManasikSchedule::class => Policies\ManasikSchedulePolicy::class,
        Models\EquipmentDistribution::class => Policies\EquipmentPolicy::class,
        Models\Commission::class => Policies\CommissionPolicy::class,
        Models\Announcement::class => Policies\AnnouncementPolicy::class,
        Models\User::class => Policies\UserPolicy::class,
    ];

    public function register(): void
    {
        $this->app->singleton(SettingService::class);
        $this->app->singleton(BranchContext::class);
        $this->app->singleton(AccessContext::class);
        $this->app->singleton(FileVault::class);
        $this->app->singleton(ActivityLogService::class);
    }

    public function boot(): void
    {
        // Register policies explicitly.
        foreach (self::POLICIES as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // White-label settings are managed only by the owner.
        Gate::define('manage-settings', fn ($user) => $user->isSuperAdmin());
        Gate::define('view-activity-log', fn ($user) => $user->isStaff());

        $this->configureProxiesAndScheme();

        // Rate-limit login attempts (5 / minute / email+ip).
        RateLimiter::for('login', function ($request) {
            $key = strtolower((string) $request->input('email')).'|'.$request->ip();

            return [Limit::perMinute(5)->by($key)];
        });
    }

    /**
     * Skema URL & proxy tepercaya.
     *
     * HTTPS TIDAK lagi dipaksa hanya karena APP_ENV=production — hosting tanpa
     * sertifikat SSL akan membuat seluruh URL (termasuk form login) mengarah ke
     * https yang tidak bisa dibuka. Sekarang HTTPS dipaksa bila APP_URL memang
     * https, atau bila FORCE_HTTPS di-set eksplisit.
     */
    private function configureProxiesAndScheme(): void
    {
        // Di belakang Cloudflare / nginx / load balancer, tanpa ini IP asli
        // pengunjung terbaca sebagai IP proxy sehingga rate limit login
        // (5x per menit per email+IP) tercampur antar pengguna.
        if ($proxies = config('app.trusted_proxies')) {
            TrustProxies::at($proxies === '*' ? '*' : array_map('trim', explode(',', (string) $proxies)));
            TrustProxies::withHeaders(
                Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_AWS_ELB
            );
        }

        $force = config('app.force_https');

        if ($force === null) {
            $force = str_starts_with((string) config('app.url'), 'https://');
        }

        if (filter_var($force, FILTER_VALIDATE_BOOLEAN)) {
            URL::forceScheme('https');
        }
    }
}
