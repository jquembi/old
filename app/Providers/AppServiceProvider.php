<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Volt\Volt;
use App\Listeners\RecordGeoAccessDecision;
use JoseQuembi\AngolaGeoGuard\Events\GeoAccessAllowed;
use JoseQuembi\AngolaGeoGuard\Events\GeoAccessDenied;
use JoseQuembi\AngolaGeoGuard\Events\GeoAccessRequiresVerification;

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
        $this->configureDefaults();
        $this->configureRateLimiting();
        Event::listen(GeoAccessAllowed::class, RecordGeoAccessDecision::class);
        Event::listen(GeoAccessDenied::class, RecordGeoAccessDecision::class);
        Event::listen(GeoAccessRequiresVerification::class, RecordGeoAccessDecision::class);

        Volt::mount([
            resource_path('views/pages'),
        ]);
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
            : null,
        );
    }

    /**
     * Configure rate limiting for API routes.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
