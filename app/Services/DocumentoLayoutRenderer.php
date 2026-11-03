<?php

namespace App\Services;

use App\Models\DocumentoLayout;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;

/**
 * Desenha os blocos de um layout de documento (logo, texto, linhas, QR, marca de água…)
 * sobre uma página FPDI. Todas as coordenadas dos blocos são percentagens da página.
 */
class DocumentoLayoutRenderer
{
    /** @var array<string, \BaconQrCode\Encoder\QrCode> */
    protected static array $qrCache = [];

    public static function aplicaNaPagina(string $regra, int $pagina, int $total): bool
    {
        return match ($regra) {
            'primeira' => $pagina === 1,
            'ultima' => $pagina === $total,
            'exceto_primeira' => $pagina > 1,
            'impares' => $pagina % 2 === 1,
            'pares' => $pagina % 2 === 0,
            default => true,
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocos
     * @param  array{qr_url?:string, tipo_nome?:string, titulo?:string, institucional?:array}  $ctx
     */
    public static function renderizar(DocumentoPdf $pdf, array $blocos, int $pagina, int $total, float $pw, float $ph, array $ctx = []): void
    {
        $inst = $ctx['institucional'] ?? DocumentoLayout::institucional();
        $vars = [
            '{{pagina}}' => (string) $pagina,
            '{{total}}' => (string) $total,
            '{{data}}' => now()->format('d/m/Y'),
            '{{hora}}' => now()->format('H:i'),
            '{{tipo}}' => (string) ($ctx['tipo_nome'] ?? ''),
            '{{titulo}}' => (string) ($ctx['titulo'] ?? ''),
            '{{orgao}}' => (string) ($inst['orgao'] ?? ''),
            '{{subtitulo}}' => (string) ($inst['subtitulo'] ?? ''),
            '{{morada}}' => (string) ($inst['morada'] ?? ''),
            '{{contactos}}' => (string) ($inst['contactos'] ?? ''),
        ];

        foreach ($blocos as $bloco) {
            if (! is_array($bloco) || ! static::aplicaNaPagina((string) ($bloco['pagina'] ?? 'todas'), $pagina, $total)) {
                continue;
            }
            if (! empty($bloco['oculto'])) {
                continue;
            }

            $x = (float) ($bloco['x'] ?? 0) / 100 * $pw;
            $y = (float) ($bloco['y'] ?? 0) / 100 * $ph;
            $w = (float) ($bloco['w'] ?? 10) / 100 * $pw;
            $h = (float) ($bloco['h'] ?? 5) / 100 * $ph;
            $p = (array) ($bloco['props'] ?? []);

            match ($bloco['tipo'] ?? '') {
                'logo' => static::logo($pdf, $inst, $x, $y, $w, $h),
                'texto' => static::texto($pdf, strtr((string) ($p['texto'] ?? ''), $vars), $p, $x, $y, $w),
                'linha' => static::linha($pdf, $p, $x, $y, $w, $h),
                'retangulo' => static::retangulo($pdf, $p, $x, $y, $w, $h),
                'marca_agua' => static::marcaAgua($pdf, strtr((string) ($p['texto'] ?? ''), $vars), $p, $x, $y, $w, $h),
                'qr' => static::qr($pdf, $p, $x, $y, $w, (string) ($ctx['qr_url'] ?? ''), $ctx['tipo_nome'] ?? ''),
                default => null,
            };
        }
    }

    protected static function logo(DocumentoPdf $pdf, array $inst, float $x, float $y, float $w, float $h): void
    {
        $ficheiro = public_path((string) ($inst['logo'] ?? ''));
        if (! is_file($ficheiro) || ! ($dim = @getimagesize($ficheiro))) {
            return;
        }
        $escala = min($w / $dim[0], $h / $dim[1]);
        $lw = $dim[0] * $escala;
        $lh = $dim[1] * $escala;
        $pdf->Image($ficheiro, $x + ($w - $lw) / 2, $y + ($h - $lh) / 2, $lw, $lh);
    }

    protected static function texto(DocumentoPdf $pdf, string $texto, array $p, float $x, float $y, float $w): void
    {
        if ($texto === '') {
            return;
        }
        $fonte = in_array($p['fonte'] ?? 'Helvetica', ['Helvetica', 'Times', 'Courier'], true) ? $p['fonte'] : 'Helvetica';
        $estilo = (! empty($p['negrito']) ? 'B' : '').(! empty($p['italico']) ? 'I' : '');
        $tamanho = max(4.0, min(72.0, (float) ($p['tamanho'] ?? 10)));
        [$r, $g, $b] = static::rgb($p['cor'] ?? '#18181b');

        $pdf->SetFont($fonte, $estilo, $tamanho);
        $pdf->SetTextColor($r, $g, $b);
        $pdf->SetXY($x, $y);
        $alinh = in_array($p['alinhamento'] ?? 'L', ['L', 'C', 'R', 'J'], true) ? $p['alinhamento'] : 'L';
        $pdf->MultiCell($w, $tamanho * 0.3528 * 1.25, static::latin($texto), 0, $alinh);
    }

    protected static function linha(DocumentoPdf $pdf, array $p, float $x, float $y, float $w, float $h): void
    {
        [$r, $g, $b] = static::rgb($p['cor'] ?? '#d4d4d8');
        $pdf->SetDrawColor($r, $g, $b);
        $pdf->SetLineWidth(max(0.05, (float) ($p['espessura'] ?? 0.3)));
        if ($h > $w) {
            $pdf->Line($x, $y, $x, $y + $h);
        } else {
            $pdf->Line($x, $y, $x + $w, $y);
        }
    }

    protected static function retangulo(DocumentoPdf $pdf, array $p, float $x, float $y, float $w, float $h): void
    {
        $fundo = ! empty($p['cor_fundo']);
        $borda = ! empty($p['cor_borda']) && (float) ($p['espessura'] ?? 0.3) > 0;
        if (! $fundo && ! $borda) {
            return;
        }
        if ($fundo) {
            $pdf->SetFillColor(...static::rgb($p['cor_fundo']));
        }
        if ($borda) {
            $pdf->SetDrawColor(...static::rgb($p['cor_borda']));
            $pdf->SetLineWidth((float) ($p['espessura'] ?? 0.3));
        }
        $pdf->Rect($x, $y, $w, $h, ($fundo && $borda) ? 'DF' : ($fundo ? 'F' : 'D'));
    }

    protected static function marcaAgua(DocumentoPdf $pdf, string $texto, array $p, float $x, float $y, float $w, float $h): void
    {
        if ($texto === '') {
            return;
        }
        $tamanho = max(10.0, min(200.0, (float) ($p['tamanho'] ?? 60)));
        $angulo = (float) ($p['angulo'] ?? 45);
        $cx = $x + $w / 2;
        $cy = $y + $h / 2;

        $pdf->SetFont('Helvetica', 'B', $tamanho);
        $pdf->SetTextColor(...static::rgb($p['cor'] ?? '#e4e4e7'));
        $largura = $pdf->GetStringWidth(static::latin($texto));

        $a = deg2rad($angulo);
        $k = $pdf->fatorEscala();
        $hPage = $pdf->GetPageHeight();
        $pdf->_outRaw(sprintf(
            'q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',
            cos($a), sin($a), -sin($a), cos($a), $cx * $k, ($hPage - $cy) * $k, -$cx * $k, -($hPage - $cy) * $k
        ));
        $pdf->Text($cx - $largura / 2, $cy + $tamanho * 0.3528 / 3, static::latin($texto));
        $pdf->_outRaw('Q');
    }

    protected static function qr(DocumentoPdf $pdf, array $p, float $x, float $y, float $w, string $url, string $tipoNome): void
    {
        if ($url === '') {
            return;
        }
        $matriz = static::matrizQr($url, (string) ($p['nivel'] ?? 'M'))->getMatrix();
        $moldura = ! empty($p['moldura']);
        $pad = $moldura ? 1.5 : 0.0;
        $rotulo = (string) ($p['rotulo'] ?? '');
        $total = max(12.0, $w);
        $lado = $total - 2 * $pad - ($rotulo !== '' ? 2.5 : 0.0);

        [$rf, $gf, $bf] = static::rgb($p['cor_fundo'] ?? '#ffffff');
        $pdf->SetFillColor($rf, $gf, $bf);
        if ($moldura) {
            $pdf->SetDrawColor(...static::rgb($p['cor_moldura'] ?? '#e4e4e7'));
            $pdf->SetLineWidth(0.3);
            $pdf->Rect($x, $y, $total, $total, 'DF');
        } else {
            $pdf->Rect($x, $y, $total, $total, 'F');
        }

        $n = $matriz->getWidth();
        $mod = $lado / $n;
        $ox = $x + ($total - $lado) / 2;
        $oy = $rotulo !== '' ? $y + $pad + 0.5 : $y + ($total - $lado) / 2;
        $pdf->SetFillColor(...static::rgb($p['cor'] ?? '#18181b'));
        for ($j = 0; $j < $n; $j++) {
            for ($i = 0; $i < $n; $i++) {
                if ($matriz->get($i, $j) === 1) {
                    $pdf->Rect($ox + $i * $mod, $oy + $j * $mod, $mod + 0.01, $mod + 0.01, 'F');
                }
            }
        }

        if ($rotulo !== '') {
            $pdf->SetTextColor(...static::rgb($p['cor'] ?? '#18181b'));
            $pdf->SetFont('Helvetica', 'B', 4.5);
            $pdf->SetXY($x, $y + $total - 2.8);
            $pdf->Cell($total, 2.2, static::latin($rotulo), 0, 0, 'C');
        }
    }

    protected static function matrizQr(string $url, string $nivel): \BaconQrCode\Encoder\QrCode
    {
        $nivel = in_array($nivel, ['L', 'M', 'Q', 'H'], true) ? $nivel : 'M';

        return static::$qrCache[$nivel.$url] ??= Encoder::encode(
            $url,
            match ($nivel) {
                'L' => ErrorCorrectionLevel::L(),
                'Q' => ErrorCorrectionLevel::Q(),
                'H' => ErrorCorrectionLevel::H(),
                default => ErrorCorrectionLevel::M(),
            },
            'UTF-8'
        );
    }

    public static function latin(string $texto): string
    {
        return (string) (@iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $texto) ?: $texto);
    }

    /** @return array{int,int,int} */
    public static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return [24, 24, 27];
        }

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }
}
