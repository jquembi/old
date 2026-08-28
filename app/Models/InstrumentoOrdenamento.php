<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Shared\Traits\HasMunicipioScope;
use App\Shared\Traits\HasSlug;
use App\Shared\Traits\HasAuditFields;

class InstrumentoOrdenamento extends Model
{
    use HasFactory, SoftDeletes, HasMunicipioScope, HasSlug, HasAuditFields;

    protected $table = 'instrumentos_ordenamento';

    protected $fillable = [
        'municipio_id',
        'titulo',
        'slug',
        'tipo',
        'descricao',
        'numero_documento',
        'data_criacao',
        'data_aprovacao',
        'data_publicacao',
        'versao',
        'file_path',
        'status',
        'aprovado_por',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data_criacao' => 'date',
            'data_aprovacao' => 'date',
            'data_publicacao' => 'date',
        ];
    }

    protected $slugSource = 'titulo';

    // ===== RELACIONAMENTOS =====

    /**
     * Município do instrumento.
     */
    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    /**
     * Usuário criador.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuário aprovador.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

    // ===== SCOPES =====

    /**
     * Apenas instrumentos aprovados.
     */
    public function scopeAprovados($query)
    {
        return $query->where('status', 'aprovado');
    }

    /**
     * Instrumentos pendentes.
     */
    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    /**
     * Por tipo de instrumento.
     */
    public function scopeDoTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Por versão.
     */
    public function scopeVersao($query, string $versao)
    {
        return $query->where('versao', $versao);
    }

    /**
     * Publicados.
     */
    public function scopePublicados($query)
    {
        return $query->whereNotNull('data_publicacao');
    }

    // ===== MÉTODOS HELPER =====

    /**
     * Verifica se está aprovado.
     */
    public function isApproved(): bool
    {
        return $this->status === 'aprovado';
    }

    /**
     * Verifica se está pendente.
     */
    public function isPending(): bool
    {
        return $this->status === 'pendente';
    }

    /**
     * Verifica se foi publicado.
     */
    public function isPublished(): bool
    {
        return $this->data_publicacao !== null;
    }

    /**
     * Aprova o instrumento.
     */
    public function approve(User $user): self
    {
        $this->update([
            'status' => 'aprovado',
            'data_aprovacao' => now()->toDateString(),
            'aprovado_por' => $user->id,
        ]);
        return $this;
    }

    /**
     * Publica o instrumento.
     */
    public function publish(): self
    {
        $this->update([
            'data_publicacao' => now()->toDateString(),
            'status' => 'publicado',
        ]);
        return $this;
    }

    /**
     * Cria nova versão.
     */
    public function createNewVersion(): self
    {
        $newVersion = $this->replicate();
        $currentVersion = intval(preg_replace('/[^0-9]/', '', $this->versao));
        $newVersion->versao = "v" . ($currentVersion + 1);
        $newVersion->status = 'pendente';
        $newVersion->data_aprovacao = null;
        $newVersion->aprovado_por = null;
        $newVersion->save();
        return $newVersion;
    }

    /**
     * Get tipo label em português.
     */
    public function getTipoLabel(): string
    {
        return match ($this->tipo) {
            'pmot' => 'Plano Municipal de Ordenamento do Território',
            'pdm' => 'Plano de Desenvolvimento Municipal',
            'pmdr' => 'Plano Municipal de Desenvolvimento Rural',
            'foral' => 'Foral Municipal',
            default => ucfirst($this->tipo),
        };
    }

    /**
     * Get status badge.
     */
    public function getStatusBadge(): string
    {
        $badgeClass = match ($this->status) {
            'pendente' => 'warning',
            'aprovado' => 'success',
            'publicado' => 'info',
            'arquivado' => 'dark',
            default => 'light',
        };

        $label = ucfirst($this->status);
        return "<span class='badge bg-{$badgeClass}'>{$label}</span>";
    }

    /**
     * Informações completas.
     */
    public function getFullInfo(): string
    {
        $info = $this->titulo . " ({$this->getTipoLabel()})";
        if ($this->versao) {
            $info .= " - {$this->versao}";
        }
        if ($this->numero_documento) {
            $info .= " | Doc: {$this->numero_documento}";
        }
        return $info;
    }
}
