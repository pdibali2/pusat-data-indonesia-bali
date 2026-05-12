<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL; 
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;
use App\Models\Metadata;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
            $allKlasifikasi = Metadata::query()
            ->where('status', 2)
            ->whereNotNull('klasifikasi')
            ->distinct()
            ->orderBy('klasifikasi')
            ->pluck('klasifikasi')

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