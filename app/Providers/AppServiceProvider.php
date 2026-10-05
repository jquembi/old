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

        \Illuminate\Support\Facades\View::addNamespace('pages', resource_path('views/pages'));
        \Illuminate\Support\Facades\Blade::anonymousComponentPath(resource_path('views/pages'), 'pages');

        \Livewire\Livewire::resolveMissingComponent(function (string $name) {
            if (str_starts_with($name, 'pages::')) {
                $alias = substr($name, 7);
                $class = app(\Livewire\Volt\ComponentResolver::class)->resolve(
                    $alias,
                    [resource_path('views/pages')]
                );
                if ($class) {
                    \Livewire\Livewire::component($alias, $class);
                    \Livewire\Livewire::component($name, $class);
                    return $class;
                }
            }
            return null;
        });

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
