<?php

namespace App\Http\Middleware;

use App\Models\Municipio;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para verificar se o utilizador tem acesso ao município da rota.
 *
 * - INOTU (nível 1): acesso a todos os municípios
 * - Provincial (nível 2): apenas municípios da sua província
 * - Municipal/Técnico (nível 3-4): apenas o seu município
 */
class VerificarAcessoMunicipio
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->activo) {
            abort(403, 'Acesso negado.');
        }

        // INOTU tem acesso a todos os municípios
        if ($user->isInotu()) {
            return $next($request);
        }

        // Obter o município da rota (parâmetro {municipio})
        $municipio = $request->route('municipio');

        if (!$municipio) {
            return $next($request);
        }

        // Resolver o modelo se vier como slug/id
        if (!$municipio instanceof Municipio) {
            $municipio = Municipio::where('slug', $municipio)
                ->orWhere('id', $municipio)
                ->first();

            if (!$municipio) {
                abort(404, 'Município não encontrado.');
            }
        }

        // Provincial: só municípios da sua província
        if ($user->isProvincial()) {
            if ($user->provincia_id !== $municipio->provincia_id) {
                abort(403, 'Não tem acesso a este município.');
            }

            return $next($request);
        }

        // Municipal/Técnico: só o seu município
        if ($user->isMunicipal()) {
            if ($user->municipio_id !== $municipio->id) {
                abort(403, 'Não tem acesso a este município.');
            }

            return $next($request);
        }

        abort(403, 'Acesso negado.');
    }
}
