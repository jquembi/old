<?php

use App\Services\InstrumentoDocumentoExtractorService;

test('extrai ano e estado de texto de plano municipal com contexto', function () {
    $service = new InstrumentoDocumentoExtractorService();

    $texto = <<<TEXT
    REPÚBLICA DE ANGOLA
    GOVERNO PROVINCIAL DA HUÍLA
    ADMINISTRAÇÃO MUNICIPAL DO LUBANGO
    
    PLANO MUNICIPAL DE ORDENAMENTO DO TERRITÓRIO
    
    Aprovado por Decreto Executivo n.º 45/2024 para o horizonte temporal 2024-2030.
    O presente plano estabelece o modelo de desenvolvimento espacial do Município do Lubango.
    TEXT;

    $resultado = $service->analisarTexto($texto, 'pmot_lubango.pdf', 'Plano Municipal de Ordenamento do Território', 'Lubango');

    expect($resultado['ano'])->toBe('2024');
    expect($resultado['ano_detectado'])->toBeTrue();
    expect($resultado['estado'])->toBe('aprovado');
    expect(strtolower($resultado['titulo']))->toContain(strtolower('Plano Municipal de Ordenamento do Território'));
});

test('detecta ano a partir do nome do ficheiro quando ausente no corpo', function () {
    $service = new InstrumentoDocumentoExtractorService();

    $texto = "Documento técnico de planeamento sem data explícita no corpo.";
    $resultado = $service->analisarTexto($texto, 'PMOT_Cuvango_2025_Final.pdf', 'PMOT', 'Cuvango');

    expect($resultado['ano'])->toBe('2025');
    expect($resultado['ano_detectado'])->toBeTrue();
});

test('extrai texto e metadados de PDF existente no projecto', function () {
    $service = new InstrumentoDocumentoExtractorService();
    $pdfPath = __DIR__ . '/../../public/Documentacao/PROJECTO DO G.TERRAS.pdf';

    if (! file_exists($pdfPath)) {
        test()->markTestSkipped('Ficheiro de teste nao encontrado.');
    }

    $resultado = $service->extrairDeFicheiro($pdfPath, 'PROJECTO DO G.TERRAS.pdf', 'Plano Municipal de Ordenamento do Território', 'Lubango');

    expect($resultado['sucesso'])->toBeTrue();
    expect($resultado['dados']['num_paginas'])->toBeGreaterThanOrEqual(1);
    expect($resultado['dados']['titulo'])->not->toBeEmpty();
    expect($resultado['dados']['descricao'])->toContain('BASE DE DADOS');
});
