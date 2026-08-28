<?php

use App\Services\CroquiPdfDataExtractor;

function criarDocxTeste(array $linhas): string
{
    if (! class_exists(\ZipArchive::class)) {
        test()->markTestSkipped('Extensao ZipArchive indisponivel.');
    }

    $temp = tempnam(sys_get_temp_dir(), 'inotu_docx_test_');
    $zip = new \ZipArchive();

    expect($zip->open($temp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE))->toBeTrue();

    $paragrafos = collect($linhas)
        ->map(fn (string $linha) => '<w:p><w:r><w:t>' . htmlspecialchars($linha, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</w:t></w:r></w:p>')
        ->implode('');

    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"></Types>');
    $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>' . $paragrafos . '</w:body></w:document>');
    $zip->close();

    $conteudo = file_get_contents($temp);
    @unlink($temp);

    return $conteudo ?: '';
}

function criarDocxTesteComXmlBruto(string $xmlBody): string
{
    if (! class_exists(\ZipArchive::class)) {
        test()->markTestSkipped('Extensao ZipArchive indisponivel.');
    }

    $temp = tempnam(sys_get_temp_dir(), 'inotu_docx_raw_test_');
    $zip = new \ZipArchive();

    expect($zip->open($temp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE))->toBeTrue();

    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"></Types>');
    $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>' . $xmlBody . '</w:body></w:document>');
    $zip->close();

    $conteudo = file_get_contents($temp);
    @unlink($temp);

    return $conteudo ?: '';
}

test('extrai dados de um documento Word docx', function () {
    $conteudo = criarDocxTeste([
        'Requerente: Maria Antonia',
        'Area: 123,45 m2',
        'Perimetro: 88 m',
        'Escala: 1/25000',
        'Tecnico Responsavel: Joao Silva',
        'Data: 05/2026',
    ]);

    $resultado = (new CroquiPdfDataExtractor())->extrairDeConteudo($conteudo, 'memoria.docx');

    expect($resultado['preenchido'])->toBeTrue()
        ->and($resultado['dados']['proprietarios'][0]['nome'])->toBe('Maria Antonia')
        ->and($resultado['dados']['area'])->toBe('123.45')
        ->and($resultado['dados']['perimetro'])->toBe('88')
        ->and($resultado['dados']['escala'])->toBe('1/25000')
        ->and($resultado['dados']['tecnico_responsavel'])->toBe('Joao Silva')
        ->and($resultado['dados']['ano'])->toBe('2026');
});

test('extrai dados do modelo real de memoria descritiva do croquis', function () {
    $ficheiros = glob(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'Modelos de Doc' . DIRECTORY_SEPARATOR . '*Croquis.docx');

    expect($ficheiros)->not->toBeEmpty();

    $conteudo = file_get_contents($ficheiros[0]);
    expect($conteudo)->not->toBeFalse();

    $resultado = (new CroquiPdfDataExtractor())->extrairMemoriaDescritivaDeConteudo(
        $conteudo,
        basename($ficheiros[0])
    );

    expect($resultado['preenchido'])->toBeTrue()
        ->and($resultado['dados']['proprietarios'][0]['nome'])->toBe('Abílio da Costa Cachio')
        ->and($resultado['dados']['proprietarios'][0]['contacto'])->toBe('')
        ->and($resultado['dados']['titulo'])->toBe('Croqui de Localização de Abílio da Costa Cachio')
        ->and($resultado['dados']['area'])->toBe('800.00')
        ->and($resultado['dados']['perimetro'])->toBe('120.00')
        ->and($resultado['dados']['dimensao'])->toBe('40,00 m x 20,00 m')
        ->and($resultado['dados']['escala'])->toBe('1/25000')
        ->and($resultado['dados']['tecnico_responsavel'])->toBe('Isaias Calunga Etumbu Tchitemo')
        ->and($resultado['dados']['data_folha'])->toBe('05/2026')
        ->and($resultado['dados'])->not->toHaveKey('finalidade')
        ->and($resultado['dados']['coordenadas'])->toHaveCount(4)
        ->and($resultado['dados']['coordenadas'][0])->toMatchArray([
            'lat_gms' => '15°01\'37.51"S',
            'lat_utm' => '8338077.24',
            'lon_gms' => '13°25\'41.48"E',
            'lon_utm' => '331021.56',
        ]);
});

test('normaliza dados extraidos com bytes invalidos para json do Livewire', function () {
    $conteudo = criarDocxTesteComXmlBruto(
        '<w:p><w:r><w:t>Requerente: Jos' . chr(0xE9) . ' Silva</w:t></w:r></w:p>'
        . '<w:p><w:r><w:t>Area: 50 m2</w:t></w:r></w:p>'
    );

    $resultado = (new CroquiPdfDataExtractor())->extrairMemoriaDescritivaDeConteudo($conteudo, 'memoria.docx');

    expect($resultado['dados']['proprietarios'][0]['nome'])->toBe('José Silva')
        ->and(json_encode($resultado, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE))->toBeString();
});

test('extrai numero do bilhete no formato da memoria descritiva', function () {
    $conteudo = criarDocxTeste([
        'Nome: Abílio da Costa Cachio',
        'BI/ NIF n.º: 001234567LA045',
        'Telefone: ___________________________',
        'Área Total: 800,00 m²',
    ]);

    $resultado = (new CroquiPdfDataExtractor())->extrairMemoriaDescritivaDeConteudo($conteudo, 'memoria.docx');

    expect($resultado['dados']['proprietarios'][0])->toMatchArray([
        'nome' => 'Abílio da Costa Cachio',
        'bi' => '001234567LA045',
        'contacto' => '',
    ]);
});
