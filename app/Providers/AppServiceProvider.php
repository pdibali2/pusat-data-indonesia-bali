<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL; 
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;
use App\Models\Klasifikasi;
// use App\Models\Metadata;
use App\Services\AuditTrailService;
use App\Services\AnomalyDetectionService;
use App\Services\WorkflowService;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ── AuditTrailService: singleton ─────────────────────────
        // Singleton agar IP/UserAgent di-resolve sekali per request,
        // dan bisa di-inject ke service lain tanpa re-instantiate.
        $this->app->singleton(AuditTrailService::class);
 
        // ── AnomalyDetectionService: singleton ───────────────────
        // Bergantung pada AuditTrailService (auto-resolved oleh container).
        $this->app->singleton(AnomalyDetectionService::class);
 
        // ── WorkflowService: singleton ───────────────────────────
        $this->app->singleton(WorkflowService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');

        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view) {

            $allKlasifikasi = Klasifikasi::query()
                ->orderBy('nama_klasifikasi')
                ->pluck('nama_klasifikasi')

                ->map(fn ($k) => trim((string) $k))

                ->filter(function ($k) {
                    return $k !== ''
                        && $k !== '-'
                        && \Illuminate\Support\Str::slug($k) !== '';
                })

                ->values();

            $view->with('allKlasifikasi', $allKlasifikasi);
        });

        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}