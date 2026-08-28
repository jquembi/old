<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LixeiraService
{
    public static function enviar(Model $model): void
    {
        $user = Auth::user();

        abort_unless($user && ($user->podeEnviarParaLixeira() || $user->temPermissao(self::permissaoEliminar($model))), 403);

        if ($model->isFillable('deleted_by') || array_key_exists('deleted_by', $model->getAttributes())) {
            $model->deleted_by = $user->id;
            $model->save();
        }

        $model->delete();
    }

    public static function restaurar(Model $model): void
    {
        abort_unless(Auth::user()?->podeAcederLixeira(), 403);

        if (method_exists($model, 'restore')) {
            $model->restore();
        }

        if ($model->isFillable('deleted_by') || array_key_exists('deleted_by', $model->getAttributes())) {
            $model->deleted_by = null;
            $model->save();
        }
    }

    public static function eliminarDefinitivo(Model $model): void
    {
        abort_unless(Auth::user()?->podeAcederLixeira(), 403);

        method_exists($model, 'forceDelete')
            ? $model->forceDelete()
            : $model->delete();
    }

    private static function permissaoEliminar(Model $model): string
    {
        $prefixos = [
            'App\Models\OperacaoUrbanistica' => 'operacoes',
            'App\Models\Instrumento' => 'instrumentos',
            'App\Models\Loteamento' => 'loteamentos',
            'App\Models\Parecer' => 'pareceres',
            'App\Models\ProjectoSona' => 'sona',
            'App\Models\Croqui' => 'operacoes',
        ];

        return ($prefixos[$model::class] ?? str($model->getTable())->before('_')->plural()->toString()) . '.eliminar';
    }
}
