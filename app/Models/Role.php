<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
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

    // ─── Níveis hierárquicos ─────────────────────────────
    public const NIVEL_INOTU = 1;
    public const NIVEL_PROVINCIAL = 2;
    public const NIVEL_MUNICIPAL = 3;
    public const NIVEL_TECNICO = 4;

    // Manter constantes legadas para compatibilidade
    public const SUPER_ADMIN = self::CHEFE_SECCAO_TECNICA;
    public const ADMIN_PROVINCIAL = self::GOVERNADOR;
    public const TECNICO_MUNICIPAL = self::TECNICO_ORDENAMENTO;
    public const TECNICO_NACIONAL = self::TECNICO_INOTU;

    public function permissoes(): BelongsToMany
    {
        return $this->belongsToMany(Permissao::class, 'role_permissao', 'role_id', 'permissao_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    public function temPermissao(string $permissao): bool
    {
        return $this->permissoes()->where('nome', $permissao)->exists();
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
}
