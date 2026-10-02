<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use RuleManager\Models\Permission as RuleManagerPermission;

class Permissao extends RuleManagerPermission
{
    protected $fillable = ['nome', 'descricao', 'grupo'];

    public function getGrupoAttribute(): ?string
    {
        return $this->modulo;
    }

    public function setGrupoAttribute(?string $value): void
    {
        $this->attributes['modulo'] = $value;
    }

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (self $permission): void {
            if (str_contains($permission->nome, '_')) {
                $permission->slug = $permission->nome;
            }
        });

        static::saved(function (self $permission): void {
            // Substitui o bypass histórico por atribuições explícitas. Assim,
            // novos privilégios continuam disponíveis aos administradores sem
            // abrir acesso para outros papéis.
            Role::query()
                ->whereIn('nome', [Role::DIRECTOR_INOTU, Role::SUPER_ADMIN_NACIONAL])
                ->each(fn (Role $role) => $role->darPermissao($permission));
        });
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, config('rule-manager.table_names.role_has_permissions'), 'permission_id', 'role_id');
    }
}
