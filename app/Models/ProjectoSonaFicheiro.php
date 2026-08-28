<?php

namespace App\Models;

use App\Models\Concerns\ComAutenticidadeQr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectoSonaFicheiro extends Model
{
    use ComAutenticidadeQr;

    protected $table = 'projecto_sona_ficheiros';

    protected $fillable = [
        'projecto_sona_id', 'nome_original', 'caminho',
        'tipo_ficheiro', 'mime_type', 'tamanho', 'fase',
        'legenda', 'enviado_por', 'qr_token', 'verificado',
    ];

    public function projectoSona(): BelongsTo
    {
        return $this->belongsTo(ProjectoSona::class);
    }

    public function enviadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enviado_por');
    }
}
