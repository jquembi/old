<?php

use App\Services\InstrumentoDocumentoExtractorService;
use App\Services\NativeOcrService;

uses(Tests\TestCase::class);

it('reconhece documentos, rotação e baixo contraste com o motor nativo', function (string $fixture) {
    if (PHP_OS_FAMILY !== 'Windows') {
        $this->markTestSkipped('Integração com o OCR nativo do Windows.');
    }
    $result = app(NativeOcrService::class)->extract(base_path('tests/Fixtures/ocr/'.$fixture));
    expect($result['sucesso'])->toBeTrue()
        ->and($result['texto'])->toContain('PLANO MUNICIPAL DE ORDENAMENTO')
        ->and($result['texto'])->toContain('LUBANGO')
        ->and($result['texto'])->toContain('2024')
        ->and($result['motor'])->toBe('windows-native');
})->with(['document.png', 'rotated.png', 'contrast.png']);

it('lê PDF digitalizado e informa o limite de páginas', function () {
    if (PHP_OS_FAMILY !== 'Windows') {
        $this->markTestSkipped('Integração com o OCR nativo do Windows.');
    }
    $path = tempnam(sys_get_temp_dir(), 'ocr-test-');
    try {
        $pdf = new FPDF;
        for ($i = 0; $i < 2; $i++) {
            $pdf->AddPage();
            $pdf->Image(base_path('tests/Fixtures/ocr/document.png'), 10, 20, 190);
        }
        $pdf->Output('F', $path);
        config(['ocr.max_pages' => 1]);
        $result = app(InstrumentoDocumentoExtractorService::class)->extrairDeFicheiro($path, 'digitalizado.pdf');
        expect($result['sucesso'])->toBeTrue()
            ->and($result['dados']['ano'])->toBe('2024')
            ->and($result['dados']['num_paginas'])->toBe(2)
            ->and($result['paginas_processadas'])->toBe(1)
            ->and($result['avisos'])->not->toBeEmpty();
    } finally {
        unlink($path);
    }
});

it('informa erros de ficheiros inválidos em vez de inventar uma leitura bem sucedida', function () {
    $path = tempnam(sys_get_temp_dir(), 'ocr-test-');
    try {
        file_put_contents($path, 'nao e uma imagem');
        $result = app(InstrumentoDocumentoExtractorService::class)->extrairDeFicheiro($path, 'invalido.png');
        expect($result['sucesso'])->toBeFalse()->and($result['texto'])->toBe('');
        $result = app(InstrumentoDocumentoExtractorService::class)->extrairDeFicheiro($path, 'plano_2024.dwg');
        expect($result['sucesso'])->toBeFalse()->and($result['dados']['titulo'])->toBe('');
    } finally {
        unlink($path);
    }
});

it('preserva entidades, parágrafos, tabelas e cabeçalhos no DOCX sem pacote de leitura', function () {
    $path = tempnam(sys_get_temp_dir(), 'ocr-docx-');
    try {
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);
        $prefix = '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>';
        $zip->addFromString('word/document.xml', $prefix.'<w:p><w:r><w:t>Plano &amp; Território</w:t></w:r></w:p><w:tbl><w:tr><w:tc><w:p><w:r><w:t>Ano: 2024</w:t></w:r></w:p></w:tc></w:tr></w:tbl></w:body></w:document>');
        $zip->addFromString('word/header1.xml', '<w:hdr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:p><w:r><w:t>Município do Lubango</w:t></w:r></w:p></w:hdr>');
        $zip->close();
        $result = app(InstrumentoDocumentoExtractorService::class)->extrairDeFicheiro($path, 'plano.docx');
        expect($result['sucesso'])->toBeTrue()
            ->and($result['texto'])->toContain("Plano & Território\nAno: 2024", 'Município do Lubango')
            ->and($result['dados']['ano'])->toBe('2024');
    } finally {
        unlink($path);
    }
});

it('não considera texto vazio nem ano do ficheiro como OCR bem sucedido', function () {
    $path = tempnam(sys_get_temp_dir(), 'ocr-empty-');
    try {
        $result = app(InstrumentoDocumentoExtractorService::class)->extrairDeFicheiro($path, 'plano_2024.txt', 'PMOT', 'Lubango');
        expect($result['sucesso'])->toBeFalse()->and($result['dados']['ano_detectado'])->toBeFalse();
    } finally {
        unlink($path);
    }
});

it('fornece o contrato de sugestões dos formulários de projectos', function () {
    $path = tempnam(sys_get_temp_dir(), 'ocr-metadata-');
    try {
        file_put_contents($path, "Projecto de infraestruturas do Lubango\nAno: 2024\nEstado: em execução");
        $result = app(InstrumentoDocumentoExtractorService::class)->extrairMetadados($path, 'projecto.txt');
        expect($result['ano_detectado'])->toBe('2024')
            ->and($result['estado_sugerido'])->toBe('em_curso')
            ->and($result['titulo_sugerido'])->not->toBeEmpty();
    } finally {
        unlink($path);
    }
});

it('preserva detalhes de imagens grandes através de recortes e informa leituras parciais', function () {
    if (PHP_OS_FAMILY !== 'Windows') {
        $this->markTestSkipped('Integração com o OCR nativo do Windows.');
    }
    $path = base_path('tests/Fixtures/ocr/large.png');
    $result = app(NativeOcrService::class)->extract($path);
    expect($result['texto'])->toContain('PLANO MUNICIPAL DO LUBANGO', 'FIM DO DOCUMENTO ANO 2024')
        ->and($result['detalhes'][0]['recortes'])->toBe(4);
    config(['ocr.max_tiles' => 1]);
    $partial = app(NativeOcrService::class)->extract($path);
    expect($partial['avisos'])->not->toBeEmpty()->and($partial['detalhes'][0]['recortes'])->toBe(1);
});
