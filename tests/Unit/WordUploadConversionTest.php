<?php

use App\Services\PdfCompressionService;
use App\Services\PdfQrStampService;
use App\Services\DocumentoAutenticidadeService;
use App\Services\DocxHtmlPreviewService;
use App\Services\WordToPdfService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(Tests\TestCase::class);

function criarDocxUploadTeste(array $linhas): string
{
    if (! class_exists(\ZipArchive::class)) {
        test()->markTestSkipped('Extensao ZipArchive indisponivel.');
    }

    $temp = tempnam(sys_get_temp_dir(), 'inotu_docx_upload_test_');
    $zip = new \ZipArchive();

    expect($zip->open($temp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE))->toBeTrue();

    $paragrafos = collect($linhas)
        ->map(fn (string $linha) => '<w:p><w:r><w:t>' . htmlspecialchars($linha, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</w:t></w:r></w:p>')
        ->implode('');

    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"></Types>');
    $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>' . $paragrafos . '</w:body></w:document>');
    $zip->close();

    return $temp;
}

test('guarda Word original e converte para PDF apenas na saida', function () {
    Storage::fake('public');

    $tempDocx = criarDocxUploadTeste([
        'Requerente: Maria Antonia',
        'Area: 123,45 m2',
    ]);

    try {
        $upload = new UploadedFile(
            $tempDocx,
            'memoria-descritiva.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true
        );

        $resultado = (new PdfCompressionService())->storeCompressed($upload, 'croquis/testes', 'public');

        $docx = Storage::disk('public')->get($resultado['path']);
        $pdfPath = (new WordToPdfService())->converterConteudo($docx, $resultado['name']);

        expect($pdfPath)->not->toBeNull();

        $pdf = file_get_contents($pdfPath);
        $pdfComQr = PdfQrStampService::processarDownload($pdf, 'application/pdf', 'https://inotu.test/verificar/abc123', 'croqui');

        expect($resultado['path'])->toEndWith('.docx')
            ->and($resultado['name'])->toBe('memoria-descritiva.docx')
            ->and(Storage::disk('public')->exists($resultado['path']))->toBeTrue()
            ->and($pdf)->toStartWith('%PDF')
            ->and($pdfComQr)->toStartWith('%PDF')
            ->and($pdfComQr)->not->toBe($pdf);

        if ($pdfPath && is_file($pdfPath)) {
            @unlink($pdfPath);
        }
    } finally {
        if (is_file($tempDocx)) {
            @unlink($tempDocx);
        }
    }
});

test('stampa qr de validacao no layout da memoria descritiva sem recriar o pdf', function () {
    $tempPdf = tempnam(sys_get_temp_dir(), 'inotu_memoria_layout_') . '.pdf';

    try {
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'MEMORIA DESCRITIVA', 0, 1, 'C');
        $pdf->Rect(150, 38, 35, 32);
        $pdf->SetXY(150, 40);
        $pdf->MultiCell(35, 5, "VALIDACAO\nQR CODE", 0, 'C');
        $pdf->Output('F', $tempPdf);

        $conteudo = file_get_contents($tempPdf);
        $comQr = PdfQrStampService::processarDownload(
            $conteudo,
            'application/pdf',
            'https://inotu.test/documentos/verificar/abc123',
            'memoria_descritiva',
            'ID: CT-33S-E0123456-N7654321'
        );
        $texto = (new \Smalot\PdfParser\Parser())->parseContent($comQr)->getText();

        expect($conteudo)->toStartWith('%PDF')
            ->and($comQr)->toStartWith('%PDF')
            ->and($comQr)->not->toBe($conteudo)
            ->and($texto)->toContain('ID: CT-33S-E0123456-N7654321');
    } finally {
        if (is_file($tempPdf)) {
            @unlink($tempPdf);
        }
    }
});

test('posiciona qr da memoria descritiva no lado direito', function () {
    $method = new ReflectionMethod(PdfQrStampService::class, 'posicaoQrMemoriaDescritiva');
    $method->setAccessible(true);

    [$x, $y, $stampSize] = $method->invoke(null, 210.0, 297.0);

    expect(round($y, 2))->toBe(10.0)
        ->and(round(210.0 - ($x + $stampSize), 2))->toBe(10.0);
});

test('url do qr da memoria descritiva informa o contexto do documento', function () {
    $url = DocumentoAutenticidadeService::gerarUrlQrSimples('abc123', 'memoria_descritiva');

    expect($url)->toContain('/documentos/verificar/abc123?documento=memoria_descritiva');
});

test('gera pre-visualizacao html simplificada para Word sem conversor externo', function () {
    $tempDocx = criarDocxUploadTeste([
        'MEMORIA DESCRITIVA',
        'Requerente: Maria Antonia',
        'BI/ NIF n.º 000123456LA041',
    ]);

    try {
        $conteudo = file_get_contents($tempDocx);
        $html = (new DocxHtmlPreviewService())->converterConteudo($conteudo);

        expect($html)
            ->toContain('MEMORIA DESCRITIVA')
            ->toContain('Requerente: Maria Antonia')
            ->toContain('000123456LA041');
    } finally {
        if (is_file($tempDocx)) {
            @unlink($tempDocx);
        }
    }
});
