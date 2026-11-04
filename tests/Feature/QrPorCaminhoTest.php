<?php

use App\Models\InstrumentoFicheiro;
use App\Services\DocumentoAutenticidadeService;
use Illuminate\Support\Facades\DB;

it('devolve o token QR do ficheiro de instrumento pelo caminho', function () {
    DB::statement('PRAGMA defer_foreign_keys = ON');

    $ficheiro = InstrumentoFicheiro::create([
        'instrumento_id' => 1,
        'nome_original' => 'pdm.pdf',
        'caminho' => 'instrumentos/1/pdm.pdf',
        'tipo_ficheiro' => 'pdf',
        'mime_type' => 'application/pdf',
        'tamanho' => 10,
        'enviado_por' => 1,
    ]);

    expect(DocumentoAutenticidadeService::buscarTokenPorCaminho('instrumentos/1/pdm.pdf'))
        ->toBe($ficheiro->qr_token)
        ->and(DocumentoAutenticidadeService::buscarTokenPorCaminho('instrumentos/1/outro.pdf'))->toBeNull();
});

it('gera token para registos antigos sem QR', function () {
    DB::statement('PRAGMA defer_foreign_keys = ON');

    $ficheiro = InstrumentoFicheiro::create([
        'instrumento_id' => 1, 'nome_original' => 'a.pdf', 'caminho' => 'instrumentos/1/a.pdf',
        'tipo_ficheiro' => 'pdf', 'mime_type' => 'application/pdf', 'tamanho' => 1, 'enviado_por' => 1,
    ]);
    $ficheiro->forceFill(['qr_token' => null])->saveQuietly();

    $token = DocumentoAutenticidadeService::buscarTokenPorCaminho('instrumentos/1/a.pdf');

    expect($token)->not->toBeEmpty()
        ->and($ficheiro->fresh()->qr_token)->toBe($token);
});
