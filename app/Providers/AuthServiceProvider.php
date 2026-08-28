<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Municipio;
use App\Models\InstrumentoOrdenamento;
use App\Models\InstrumentoUrbanistico;
use App\Models\Operacao;
use App\Models\Croqui;
use App\Models\TerrenoOcioso;
use App\Models\Parecer;
use App\Models\CartaMunicipal;
use App\Models\SonaPlano;
use App\Models\User;
use App\Policies\MunicipioPolicy;
use App\Policies\InstrumentoOrdenamentoPolicy;
use App\Policies\InstrumentoUrbanisticoPolicy;
use App\Policies\OperacaoPolicy;
use App\Policies\CroquiPolicy;
use App\Policies\TerrenoOciosoPolicy;
use App\Policies\ParecerPolicy;
use App\Policies\CartaMunicipalPolicy;
use App\Policies\SonaPlanoPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Municipio::class => MunicipioPolicy::class,
        InstrumentoOrdenamento::class => InstrumentoOrdenamentoPolicy::class,
        InstrumentoUrbanistico::class => InstrumentoUrbanisticoPolicy::class,
        Operacao::class => OperacaoPolicy::class,
        Croqui::class => CroquiPolicy::class,
        TerrenoOcioso::class => TerrenoOciosoPolicy::class,
        Parecer::class => ParecerPolicy::class,
        CartaMunicipal::class => CartaMunicipalPolicy::class,
        SonaPlano::class => SonaPlanoPolicy::class,
    ];

    /**
     * Register the application's policies.
     */
    // Policies registered via $policies property above

    /**
     * Boot the authentication services for the application.
     */
    public function boot(): void
    {
        // Registrar policies automaticamente
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // Gates para permissões gerais
        Gate::define('view-audit-logs', function (User $user) {
            return $user->hasRole('inotu');
        });

        Gate::define('view-reports', function (User $user) {
            return $user->hasAnyRole(['inotu', 'governo_provincial']);
        });

        Gate::define('manage-roles', function (User $user) {
            return $user->hasRole('inotu');
        });
    }
}
