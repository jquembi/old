<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarNivel
{
    public function handle(Request $request, Closure $next, string ...$niveis): Response
    {
        $user = $request->user();

        if (!$user || !$user->activo || $user->roles->isEmpty()) {
            abort(403, 'Acesso negado.');
        }

        // Super Admin Nacional (nível 0) tem acesso a tudo
        $niveisUser = $user->roles->pluck('nivel')->toArray();
        if (in_array(0, $niveisUser)) {
            return $next($request);
        }

        // As rotas institucionais foram criadas antes do nÃºcleo nacional.
        // Mantemos a sintaxe legada (1 = INOTU, 2 = provincial, ...), mas
        // aceitamos tambÃ©m os nÃ­veis atuais para evitar bloquear perfis
        // administrativos legÃ­timos apÃ³s a introduÃ§Ã£o do nÃºcleo nacional.
        $niveisPermitidos = collect($niveis)
            ->flatMap(function (string $nivel): array {
                $nivelLegado = (int) $nivel;
                $nivelAtual = match ($nivelLegado) {
                    1 => Role::NIVEL_INOTU,
                    2 => Role::NIVEL_PROVINCIAL,
                    3 => Role::NIVEL_MUNICIPAL,
                    4 => Role::NIVEL_TECNICO,
                    default => $nivelLegado,
                };

                return [$nivelLegado, $nivelAtual];
            })
            ->unique()
            ->values()
            ->all();

        // Verificar se alguma role do utilizador tem um nível permitido
        if (empty(array_intersect($niveisUser, $niveisPermitidos))) {
            abort(403, 'O seu nível de acesso não permite aceder a este recurso.');
        }

        return $next($request);
    }
}
