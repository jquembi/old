<?php

namespace App\Models;

use App\Models\Concerns\ComAutenticidadeQr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoteamentoFicheiro extends Model
{
    use ComAutenticidadeQr;

    protected $table = 'loteamento_ficheiros';

    protected $fillable = [
        'loteamento_id', 'nome_original', 'caminho',
        'tipo_ficheiro', 'mime_type', 'tamanho', 'enviado_por',
        'qr_token', 'verificado',
    ];

    public function loteamento(): BelongsTo
    {
        return $this->belongsTo(Loteamento::class);
    }

    public function enviadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enviado_por');
    }
}
