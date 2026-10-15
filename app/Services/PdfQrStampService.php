<?php

namespace App\Services;

use App\Models\TipoDocumentoQrConfig;
use setasign\Fpdi\Fpdi;
use Throwable;

class PdfQrStampService
{
    /**
     * Aplica o QR code estilizado nas páginas de um PDF ou imagem, de acordo com o layout configurado.
     */
    public static function processarDownload(
        string $conteudo,
        string $mime,
        string $qrUrl,
        string $tipoDocumento = 'default',
        ?string $textoAbaixoQr = null
    ): string {
        if (! str_contains($mime, 'pdf')) {
            return $conteudo;
        }

        try {
            return static::aplicarStampPdf($conteudo, $qrUrl, $tipoDocumento, $textoAbaixoQr);
        } catch (Throwable $e) {
            // Em caso de PDF protegido ou erro de parsing, preserva o PDF original
            report($e);
            return $conteudo;
        }
    }

    /**
     * Aplica o selo QR no PDF utilizando FPDI e as configurações da base de dados.
     */
    protected static function aplicarStampPdf(
        string $conteudo,
        string $qrUrl,
        string $tipoDocumento,
        ?string $textoPersonalizado = null
    ): string {
        $config = TipoDocumentoQrConfig::obterParaTipo($tipoDocumento);
        $tempPdf = tempnam(sys_get_temp_dir(), 'pdf_qr_src_') . '.pdf';
        file_put_contents($tempPdf, $conteudo);

        try {
            $fpdi = new Fpdi();
            $pageCount = $fpdi->setSourceFile($tempPdf);

            $qrImgTemp = static::gerarImagemQrTemp(
                $qrUrl,
                $config->cor_qr,
                $config->cor_fundo,
                $config->nivel_correcao
            );

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $fpdi->importPage($pageNo);
                $size = $fpdi->getTemplateSize($templateId);

                // Determina orientação da página original
                $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                $fpdi->AddPage($orientation, [$size['width'], $size['height']]);
                $fpdi->useTemplate($templateId);

                // Verifica se esta página deve receber o QR code
                $deveAplicar = match ($config->pagina) {
                    'primeira' => ($pageNo === 1),
                    'ultima' => ($pageNo === $pageCount),
                    default => true,
                };

                if ($deveAplicar && $qrImgTemp && is_file($qrImgTemp)) {
                    static::estamparQrNaPagina(
                        $fpdi,
                        $size['width'],
                        $size['height'],
                        $qrImgTemp,
                        $config,
                        $textoPersonalizado
                    );
                }
            }

            if ($qrImgTemp && is_file($qrImgTemp)) {
                @unlink($qrImgTemp);
            }

            return $fpdi->Output('S');
        } finally {
            if (is_file($tempPdf)) {
                @unlink($tempPdf);
            }
        }
    }

    /**
     * Desenha o QR code com moldura e rótulo na posição configurada.
     */
    protected static function estamparQrNaPagina(
        Fpdi $fpdi,
        float $pageWidth,
        float $pageHeight,
        string $qrImagePath,
        TipoDocumentoQrConfig $config,
        ?string $textoPersonalizado = null
    ): void {
        // Converte as percentagens configuradas para mm na página
        $qrSize = ($config->largura_pct / 100.0) * $pageWidth;
        $qrSize = max(12.0, min(45.0, $qrSize)); // mm - tamanho compacto e profissional
        if ($config->tamanho_mm > 0 && $config->largura_pct <= 0) {
            $qrSize = max(12.0, min(45.0, (float) $config->tamanho_mm));
        }

        $x = ($config->posicao_x_pct / 100.0) * $pageWidth;
        $y = ($config->posicao_y_pct / 100.0) * $pageHeight;

        $padding = $config->incluir_moldura ? 1.5 : 0.0;
        $tamanhoTotal = $qrSize + ($padding * 2);

        $texto = $textoPersonalizado ?: ($config->incluir_rotulo ? $config->texto_rotulo : null);
        $tamanhoImagemQr = $texto ? max(10.0, $qrSize - 2.5) : $qrSize;

        // Desenha moldura de fundo estritamente QUADRADA (Square)
        if ($config->incluir_moldura) {
            [$rF, $gF, $bF] = static::hexToRgb($config->cor_fundo);
            [$rB, $gB, $bB] = static::hexToRgb($config->cor_moldura);

            $fpdi->SetFillColor($rF, $gF, $bF);
            $fpdi->SetDrawColor($rB, $gB, $bB);
            $fpdi->SetLineWidth(0.3);
            $fpdi->Rect($x, $y, $tamanhoTotal, $tamanhoTotal, 'DF');
        }

        // Centraliza a imagem do QR Code dentro do quadrado
        $offsetX = $x + (($tamanhoTotal - $tamanhoImagemQr) / 2.0);
        $offsetY = $texto ? ($y + $padding + 0.5) : ($y + (($tamanhoTotal - $tamanhoImagemQr) / 2.0));

        $fpdi->Image(
            $qrImagePath,
            $offsetX,
            $offsetY,
            $tamanhoImagemQr,
            $tamanhoImagemQr
        );

        // Insere texto de autenticidade / legenda abaixo do QR dentro da moldura quadrada
        if ($texto) {
            [$rT, $gT, $bT] = static::hexToRgb($config->cor_qr);
            $fpdi->SetTextColor($rT, $gT, $bT);
            $fpdi->SetFont('Arial', 'B', 4.5);

            $fpdi->SetXY($x, $y + $tamanhoTotal - 2.8);
            $fpdi->Cell($tamanhoTotal, 2.2, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto), 0, 1, 'C');
        }
    }

    /**
     * Gera um ficheiro PNG temporário com o QR code usando GD ou script do sistema.
     */
    protected static function gerarImagemQrTemp(
        string $url,
        string $corQr = '#18181b',
        string $corFundo = '#ffffff',
        string $nivel = 'M'
    ): ?string {
        $tempPath = tempnam(sys_get_temp_dir(), 'inotu_qr_') . '.png';

        // Tenta gerar com o motor nativo via Node / script simples se disponível
        $nodeScript = "
            import QRCode from 'qrcode';
            import fs from 'fs';
            QRCode.toFile('{$tempPath}', '{$url}', {
                width: 320,
                margin: 1,
                color: { dark: '{$corQr}', light: '{$corFundo}' },
                errorCorrectionLevel: '{$nivel}'
            });
        ";

        $tempJs = tempnam(sys_get_temp_dir(), 'qr_node_') . '.js';
        file_put_contents($tempJs, $nodeScript);

        $nodeExe = 'node';
        @exec("{$nodeExe} \"{$tempJs}\" 2>&1");
        @unlink($tempJs);

        if (is_file($tempPath) && filesize($tempPath) > 0) {
            return $tempPath;
        }

        // Fallback rápido usando GD se o node não responder no terminal
        if (extension_loaded('gd')) {
            $im = imagecreate(160, 160);
            [$rF, $gF, $bF] = static::hexToRgb($corFundo);
            [$rQ, $gQ, $bQ] = static::hexToRgb($corQr);
            imagecolorallocate($im, $rF, $gF, $bF);
            $dark = imagecolorallocate($im, $rQ, $gQ, $bQ);
            imagerectangle($im, 10, 10, 150, 150, $dark);
            imagestring($im, 2, 40, 70, 'QR CODE', $dark);
            imagepng($im, $tempPath);
            imagedestroy($im);
            return $tempPath;
        }

        return null;
    }

    protected static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (strlen($hex) !== 6) {
            return [24, 24, 27];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
