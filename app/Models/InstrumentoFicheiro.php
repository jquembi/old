<?php

namespace App\Models;

use App\Models\Concerns\ComAutenticidadeQr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstrumentoFicheiro extends Model
{
    use ComAutenticidadeQr;

    protected $table = 'instrumento_ficheiros';

    protected $fillable = [
        'instrumento_id', 'nome_original', 'caminho',
        'tipo_ficheiro', 'mime_type', 'tamanho', 'enviado_por',
        'qr_token', 'verificado',
    ];

    public function instrumento(): BelongsTo
    {
        return $this->belongsTo(Instrumento::class);
    }

    public function enviadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enviado_por');
    }
}
