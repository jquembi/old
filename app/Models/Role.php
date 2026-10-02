<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use RuleManager\Models\Role as RuleManagerRole;

class Role extends RuleManagerRole
{
    protected $fillable = ['nome', 'descricao', 'nivel', 'grupo'];

    // ─── Grupo INOTU (nível 1) ───────────────────────────
    public const CHEFE_SECCAO_TECNICA = 'chefe_seccao_tecnica';
    public const CHEFE_SECCAO_ADMINISTRATIVA = 'chefe_seccao_administrativa';
    public const SECRETARIA_GABINETE = 'secretaria_gabinete';
    public const DIRECTOR_INOTU = 'director_inotu';
    public const TECNICO_INOTU = 'tecnico_inotu';

    // ─── Grupo Governo Provincial (nível 2) ──────────────
    public const GOVERNADOR = 'governador';
    public const VICE_GOVERNADOR_TECNICO = 'vice_governador_tecnico';
    public const ASSESSOR_VICE_GOVERNADOR = 'assessor_vice_governador';

    // ─── Grupo Administração Municipal (nível 3) ─────────
    public const ADMIN_MUNICIPAL = 'admin_municipal';
    public const ADMIN_MUNICIPAL_ADJUNTO = 'admin_municipal_adjunto';
    public const DIRECTOR_INFRAESTRUTURA = 'director_infraestrutura';

    // ─── Técnicos (nível 4) ──────────────────────────────
    public const TECNICO_ORDENAMENTO = 'tecnico_ordenamento';

    // ─── Grupos ──────────────────────────────────────────
    public const GRUPO_INOTU = 'INOTU';
    public const GRUPO_GOVERNO = 'Governo Provincial';
    public const GRUPO_ADMINISTRACAO = 'Administração Municipal';

    // ─── Nível 0: Super Admin Nacional ───────────────────
    public const SUPER_ADMIN_NACIONAL = 'super_admin_nacional';
    public const NIVEL_NACIONAL = 0;

    // ─── Nível 1: Admin Nacional ─────────────────────────
    public const ADMIN_NACIONAL = 'admin_nacional';
    public const GESTOR_NACIONAL = 'gestor_nacional';
    public const AUDITOR_NACIONAL = 'auditor_nacional';
    public const NIVEL_ADMIN_NACIONAL = 1;

    // ─── Nível 2: INOTU Central (existente) ───────────────
    public const NIVEL_INOTU = 2;

    // ─── Nível 3: Governo Provincial (existente) ──────────
    public const NIVEL_PROVINCIAL = 3;

    // ─── Nível 4: Administração Municipal (existente) ────
    public const NIVEL_MUNICIPAL = 4;

    // ─── Nível 5: Técnicos (existente) ─────────────────────
    public const NIVEL_TECNICO = 5;

    // ─── Perfis para ERP ───────────────────────────────────
    public const FINANCEIRO = 'financeiro';
    public const RH = 'rh';
    public const COMPRAS = 'compras';
    public const PATRIMONIO = 'patrimonio';
    public const CONSULTA = 'consulta';

    // Manter constantes legadas para compatibilidade
    public const SUPER_ADMIN = self::CHEFE_SECCAO_TECNICA;
    public const ADMIN_PROVINCIAL = self::GOVERNADOR;
    public const TECNICO_MUNICIPAL = self::TECNICO_ORDENAMENTO;
    public const TECNICO_NACIONAL = self::TECNICO_INOTU;

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (self $role): void {
            if (str_contains($role->nome, '_')) {
                $role->slug = $role->nome;
            }
        });

        static::created(function (self $role): void {
            if (in_array($role->nome, [self::DIRECTOR_INOTU, self::SUPER_ADMIN_NACIONAL], true)) {
                $role->darPermissao(...Permissao::query()->get()->all());
            }
        });
    }

    public function permissoes(): BelongsToMany
    {
        return $this->permissions();
    }

    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            User::class,
            'model',
            config('rule-manager.table_names.model_has_roles'),
            'role_id',
            config('rule-manager.column_names.model_morph_key', 'model_id'),
        );
    }

    public function temPermissao(string $permissao): bool
    {
        return $this->permissions()->where('slug', $permissao)->exists();
    }

    public function getNivelAttribute(): int
    {
        return (int) $this->nivel_hierarquico;
    }

    public function setNivelAttribute(int $value): void
    {
        $this->attributes['nivel_hierarquico'] = $value;
    }

    public function isInotu(): bool
    {
        return $this->nivel === self::NIVEL_INOTU;
    }

    public function isProvincial(): bool
    {
        return $this->nivel === self::NIVEL_PROVINCIAL;
    }

    public function isMunicipal(): bool
    {
        return $this->nivel === self::NIVEL_MUNICIPAL || $this->nivel === self::NIVEL_TECNICO;
    }

    public function isNacional(): bool
    {
        return in_array($this->nivel, [self::NIVEL_NACIONAL, self::NIVEL_ADMIN_NACIONAL]);
    }

    public function isErp(): bool
    {
        return in_array($this->nome, [
            self::FINANCEIRO,
            self::RH,
            self::COMPRAS,
            self::PATRIMONIO,
        ]);
    }
}
