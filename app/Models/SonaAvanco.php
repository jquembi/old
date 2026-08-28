<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Shared\Traits\HasAuditFields;

class SonaAvanco extends Model
{
    use HasFactory, SoftDeletes, HasAuditFields;

    protected $table = 'sona_avancos';

    protected $fillable = [
        'sona_fase_id',
        'titulo',
        'descricao',
        'data_avanco',
        'tipo_avanco',
        'percentual_execucao',
        'atividades_realizadas',
        'atividades_programadas',
        'obstaculos_encontrados',
        'registrado_por',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data_avanco' => 'datetime',
            'percentual_execucao' => 'integer',
        ];
    }

    // ===== RELACIONAMENTOS =====

    public function fase()
    {
        return $this->belongsTo(SonaFase::class, 'sona_fase_id');
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function imagens(): HasMany
    {
        return $this->hasMany(SonaImagem::class, 'sona_avanco_id')->orderBy('ordem');
    }

    // ===== SCOPES =====

    public function scopeTipo($query, string $tipo)
    {
        return $query->where('tipo_avanco', $tipo);
    }

    public function scopeRegistrador($query, int $userId)
    {
        return $query->where('registrado_por', $userId);
    }

    public function scopeRecentes($query, int $dias = 30)
    {
        return $query->where('data_avanco', '>=', now()->subDays($dias))->orderBy('data_avanco', 'desc');
    }

    // ===== MÉTODOS HELPER =====

    public function getTipoLabel(): string
    {
        return match ($this->tipo_avanco) {
            'analise_diagnostico' => 'Análise e Diagnóstico',
            'levantamento_campo' => 'Levantamento de Campo',
            'processamento_dados' => 'Processamento de Dados',
            'validacao_resultados' => 'Validação de Resultados',
            'reuniao_discussao' => 'Reunião e Discussão',
            'producao_cartografica' => 'Produção Cartográfica',
            'relatorio_tecnico' => 'Relatório Técnico',
            'apresentacao_stakeholders' => 'Apresentação aos Interessados',
            default => ucfirst(str_replace('_', ' ', $this->tipo_avanco)),
        };
    }

    public function addImagem(string $imagePath, ?string $descricao = null, int $ordem = 0): SonaImagem
    {
        return $this->imagens()->create([
            'image_path' => $imagePath,
            'descricao' => $descricao,
            'ordem' => $ordem,
        ]);
    }

    public function getProgressPercentage(): int
    {
        return $this->percentual_execucao ?? 0;
    }

    public function isComplete(): bool
    {
        return $this->percentual_execucao >= 100;
    }

    public function hasObstacles(): bool
    {
        return !empty($this->obstaculos_encontrados);
    }

    public function getFullInfo(): string
    {
        return "{$this->titulo} ({$this->data_avanco?->format('d/m/Y')})";
    }
}
