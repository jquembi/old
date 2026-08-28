<?php

namespace App\Services;

use App\Models\Auditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditoriaService
{
    public static function criar(Model $model, ?string $observacao = null): void
    {
        self::registrar('criar', $model, null, $model->getAttributes(), $observacao);
    }

    public static function editar(Model $model, array $valoresAntigos, ?string $observacao = null): void
    {
        self::registrar('editar', $model, $valoresAntigos, $model->getChanges(), $observacao);
    }

    private static function registrar(
        string $accao,
        Model $model,
        ?array $valoresAntigos,
        array $valoresNovos,
        ?string $observacao,
    ): void {
        try {
            Auditoria::create([
                'user_id' => Auth::id(),
                'accao' => $accao,
                'modelo' => $model::class,
                'modelo_id' => $model->getKey(),
                'valores_antigos' => $valoresAntigos,
                'valores_novos' => $valoresNovos,
                'ip_address' => Request::ip(),
                'request_id' => Request::headers()->get('X-Request-Id'),
                'method' => Request::method(),
                'url' => Request::fullUrl(),
                'route_name' => Request::route()?->getName(),
                'user_agent' => Request::userAgent(),
                'session_id' => Request::hasSession() ? Request::session()->getId() : null,
                'contexto' => $observacao ? ['observacao' => $observacao] : null,
            ]);
        } catch (\Throwable) {
            // Auditoria nao deve bloquear a operacao principal.
        }
    }
}
