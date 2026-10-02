<?php

namespace App\Models;

use App\Models\Concerns\ComAutenticidadeQr;
use App\Models\Concerns\EnviaParaLixeira;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArquivoBase extends Model
{
    use ComAutenticidadeQr, EnviaParaLixeira;

    protected $table = 'arquivos_base';

    protected $fillable = [
        'municipio_id', 'tipo', 'titulo', 'slug',
        'ficheiro_caminho', 'ficheiro_nome', 'enviado_por',
        'em_uso_por', 'em_uso_desde',
        'qr_token', 'verificado',
        'deleted_by',
        // DMS Expansion
        'versao', 'versao_anterior_id',
        'estado_aprovacao', 'aprovado_por', 'aprovado_em', 'motivo_rejeicao',
        'categoria', 'tags', 'descricao',
        'assinatura_hash', 'assinado_por', 'assinado_em',
        'sync_id', 'sync_status',
    ];

    protected $casts = [
        'em_uso_desde' => 'datetime',
        'aprovado_em' => 'datetime',
        'assinado_em' => 'datetime',
        'tags' => 'array',
    ];

    // Estados de aprovação
    public const ESTADO_RASCUNHO = 'rascunho';
    public const ESTADO_PENDENTE_APROVACAO = 'pendente_aprovacao';
    public const ESTADO_APROVADO = 'aprovado';
    public const ESTADO_REJEITADO = 'rejeitado';

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function enviadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enviado_por');
    }

    public function emUsoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'em_uso_por');
    }

    public function versaoAnterior(): BelongsTo
    {
        return $this->belongsTo(ArquivoBase::class, 'versao_anterior_id');
    }

    public function versoesPosteriores()
    {
        return $this->hasMany(ArquivoBase::class, 'versao_anterior_id');
    }

    public function aprovadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

    public function assinadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assinado_por');
    }

    // ─── Versionamento ─────────────────────────────────────

    public function criarNovaVersao(array $dados): self
    {
        $novaVersao = $this->replicate()->fill($dados);
        $novaVersao->versao = $this->versao + 1;
        $novaVersao->versao_anterior_id = $this->id;
        $novaVersao->estado_aprovacao = self::ESTADO_RASCUNHO;
        $novaVersao->sync_id = (string) \Illuminate\Support\Str::uuid();
        $novaVersao->sync_status = 'pending';
        $novaVersao->save();

        return $novaVersao;
    }

    public function getHistoricoVersoes()
    {
        $historico = collect([$this]);

        $versaoAtual = $this;
        while ($versaoAtual->versao_anterior_id) {
            $versaoAtual = ArquivoBase::find($versaoAtual->versao_anterior_id);
            if ($versaoAtual) {
                $historico->push($versaoAtual);
            } else {
                break;
            }
        }

        return $historico->sortBy('versao');
    }

    // ─── Workflow de Aprovação ───────────────────────────────

    public function submeterParaAprovacao(): void
    {
        $this->update([
            'estado_aprovacao' => self::ESTADO_PENDENTE_APROVACAO,
        ]);
    }

    public function aprovar(User $aprovador, ?string $observacao = null): void
    {
        $this->update([
            'estado_aprovacao' => self::ESTADO_APROVADO,
            'aprovado_por' => $aprovador->id,
            'aprovado_em' => now(),
            'motivo_rejeicao' => null,
        ]);

        // Log de auditoria
        app(\App\Services\AuditoriaCentralizadaService::class)->logAction(
            'approve',
            entity_type: 'ArquivoBase',
            entity_id: $this->id,
            description: "Documento {$this->titulo} aprovado por {$aprovador->name}",
            metadata: [
                'observacao' => $observacao,
                'versao' => $this->versao,
            ]
        );
    }

    public function rejeitar(User $aprovador, string $motivo): void
    {
        $this->update([
            'estado_aprovacao' => self::ESTADO_REJEITADO,
            'aprovado_por' => $aprovador->id,
            'aprovado_em' => now(),
            'motivo_rejeicao' => $motivo,
        ]);

        // Log de auditoria
        app(\App\Services\AuditoriaCentralizadaService::class)->logAction(
            'reject',
            entity_type: 'ArquivoBase',
            entity_id: $this->id,
            description: "Documento {$this->titulo} rejeitado por {$aprovador->name}",
            metadata: [
                'motivo' => $motivo,
                'versao' => $this->versao,
            ]
        );
    }

    public function isRascunho(): bool
    {
        return $this->estado_aprovacao === self::ESTADO_RASCUNHO;
    }

    public function isPendenteAprovacao(): bool
    {
        return $this->estado_aprovacao === self::ESTADO_PENDENTE_APROVACAO;
    }

    public function isAprovado(): bool
    {
        return $this->estado_aprovacao === self::ESTADO_APROVADO;
    }

    public function isRejeitado(): bool
    {
        return $this->estado_aprovacao === self::ESTADO_REJEITADO;
    }

    // ─── Assinatura Digital ───────────────────────────────────

    public function assinar(User $usuario): void
    {
        $assinaturaHash = hash('sha256', $this->id . $this->versao . $usuario->id . now());

        $this->update([
            'assinatura_hash' => $assinaturaHash,
            'assinado_por' => $usuario->id,
            'assinado_em' => now(),
        ]);

        // Log de auditoria
        app(\App\Services\AuditoriaCentralizadaService::class)->logAction(
            'sign',
            entity_type: 'ArquivoBase',
            entity_id: $this->id,
            description: "Documento {$this->titulo} assinado por {$usuario->name}",
            metadata: [
                'assinatura_hash' => $assinaturaHash,
                'versao' => $this->versao,
            ]
        );
    }

    public function verificarAssinatura(): bool
    {
        if (!$this->assinado_por || !$this->assinatura_hash) {
            return false;
        }

        $hashEsperado = hash('sha256', $this->id . $this->versao . $this->assinado_por . $this->assinado_em);

        return hash_equals($hashEsperado, $this->assinatura_hash);
    }

    // ─── Pesquisa Avançada ───────────────────────────────────

    public function scopePorCategoria($query, string $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorTags($query, array $tags)
    {
        return $query->whereJsonContains('tags', $tags);
    }

    public function scopePorEstadoAprovacao($query, string $estado)
    {
        return $query->where('estado_aprovacao', $estado);
    }

    public function scopeAprovados($query)
    {
        return $query->where('estado_aprovacao', self::ESTADO_APROVADO);
    }

    public function scopePendentesAprovacao($query)
    {
        return $query->where('estado_aprovacao', self::ESTADO_PENDENTE_APROVACAO);
    }

    public function scopePesquisa($query, string $termo)
    {
        return $query->where(function ($q) use ($termo) {
            $q->where('titulo', 'like', "%{$termo}%")
              ->orWhere('descricao', 'like', "%{$termo}%")
              ->orWhere('ficheiro_nome', 'like', "%{$termo}%")
              ->orWhereJsonContains('tags', $termo);
        });
    }

    /** Considera o lock expirado após 2 horas sem actividade */
    public function estaEmUso(): bool
    {
        return $this->em_uso_por !== null
            && $this->em_uso_desde !== null
            && $this->em_uso_desde->gt(now()->subHours(2));
    }

    public function bloquear(int $userId): void
    {
        $this->update(['em_uso_por' => $userId, 'em_uso_desde' => now()]);
    }

    public function liberar(int $userId): void
    {
        if ((int) $this->em_uso_por === $userId) {
            $this->update(['em_uso_por' => null, 'em_uso_desde' => null]);
        }
    }
}
