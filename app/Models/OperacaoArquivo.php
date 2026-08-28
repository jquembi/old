<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperacaoArquivo extends Model
{
    use HasFactory;

    protected $table = 'operacao_arquivos';

    protected $fillable = [
        'operacao_id',
        'nome_original',
        'caminho',
        'tipo_mime',
        'tamanho_bytes',
        'descricao',
        'uploaded_by',
    ];

    protected $casts = [
        'tamanho_bytes' => 'integer',
    ];

    public function operacao()
    {
        return $this->belongsTo(Operacao::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getTamanhoFormatadoAttribute(): string
    {
        $bytes = $this->tamanho_bytes;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}
