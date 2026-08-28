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
    ];

    protected $casts = [
        'em_uso_desde' => 'datetime',
    ];

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
