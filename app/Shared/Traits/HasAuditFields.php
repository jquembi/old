<?php

namespace App\Shared\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Trait para adicionar campos de auditoria a um modelo.
 *
 * Adiciona os campos: created_by, updated_by, deleted_by
 * E registra automaticamente o ID do usuário autenticado.
 */
trait HasAuditFields
{
    public static function bootHasAuditFields(): void
    {
        static::creating(function (Model $model) {
            $model->created_by = Auth::id();
        });

        static::updating(function (Model $model) {
            $model->updated_by = Auth::id();
        });

        static::deleting(function (Model $model) {
            if (isset($model->deleted_by)) {
                $model->deleted_by = Auth::id();
                $model->save();
            }
        });
    }

    /**
     * Retorna o usuário que criou o registro.
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Retorna o usuário que atualizou o registro.
     */
    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Retorna o usuário que deletou o registro.
     */
    public function deletedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'deleted_by');
    }
}
