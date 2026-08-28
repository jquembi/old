<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Provider para registrar bindings e singletons de repositórios e serviços.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repositories
        // $this->app->bind(
        //     MunicipioRepositoryContract::class,
        //     EloquentMunicipioRepository::class
        // );

        // Services
        // $this->app->singleton(MunicipioService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
