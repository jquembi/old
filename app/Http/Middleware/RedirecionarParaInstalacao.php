<?php

namespace App\Http\Middleware;

use App\Services\InstaladorService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class RedirecionarParaInstalacao
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->runningUnitTests()) {
            return $next($request);
        }

        $instalado = InstaladorService::instalado();

        if (! $instalado) {
            // O middleware é prepended ao grupo web: prepara chave, sessão e cache
            // antes de EncryptCookies, StartSession e do throttle das rotas.
            $gerarChave = blank(config('app.key'));

            InstaladorService::prepararAmbienteWeb();

            // Se havia config cache, removê-lo garante que os próximos pedidos
            // carreguem o APP_KEY persistido no .env.
            if ($gerarChave) {
                Artisan::call('config:clear');
            }
        }

        if ($request->is('instalar', 'instalar/*', 'up') || $instalado) {
            return $next($request);
        }

        return redirect('/instalar');
    }
}
