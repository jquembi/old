<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
    ];

    // ===== RELACIONAMENTOS =====

    /**
     * Roles que têm esta permissão.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role')
                    ->withTimestamps();
    }

    // ===== SCOPES =====

    public function scopeByName($query, string $name)
    {
        return $query->where('name', $name);
    }

    public function scopeCategoria($query, string $categoria)
    {
        return $query->where('name', 'like', "{$categoria}_%");
    }

    // ===== MÉTODOS HELPER =====

    /**
     * Extrai categoria da permissão (ex: 'municipio' de 'municipio_view').
     */
    public function getCategoria(): string
    {
        return strtok($this->name, '_');
    }

    /**
     * Extrai ação da permissão (ex: 'view' de 'municipio_view').
     */
    public function getAcao(): string
    {
        $parts = explode('_', $this->name, 2);
        return $parts[1] ?? '';
    }

    /**
     * Obtém label em português.
     */
    public function getLabel(): string
    {
        $categoria = $this->getCategoria();
        $acao = $this->getAcao();
        
        $categoriaLabel = match ($categoria) {
            'municipio' => 'Municípios',
            'instrumento' => 'Instrumentos',
            'croqui' => 'Croquis',
            'terreno' => 'Terrenos Ociosos',
            'parecer' => 'Pareceres',
            'sona' => 'Planos SONA',
            'auditoria' => 'Mensagens de Auditoria',
            'usuario' => 'Usuários',
            'acesso' => 'Controle de Acesso',
            default => ucfirst($categoria),
        };

        $acaoLabel = match ($acao) {
            'view' => 'Ver',
            'create' => 'Criar',
            'edit' => 'Editar',
            'delete' => 'Deletar',
            'approve' => 'Aprovar',
            'manage' => 'Gerenciar',
            default => ucfirst($acao),
        };

        return "{$acaoLabel} {$categoriaLabel}";
    }
}
