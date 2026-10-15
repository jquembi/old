<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

class NativeOcrService
{
    public function extract(string $path, string $format = 'image'): array
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            throw new RuntimeException('O OCR nativo requer Windows 10/11. Não foi usado nenhum serviço externo.');
        }
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('O ficheiro não está disponível para leitura.');
        }
        if (filesize($path) > 50 * 1024 * 1024) {
            throw new RuntimeException('O ficheiro excede o limite de 50 MB.');
        }
        if (! in_array($format, ['image', 'pdf'], true)) {
            throw new RuntimeException('Formato não suportado pelo OCR nativo.');
        }
        $windows = getenv('SystemRoot') ?: 'C:\\Windows';
        $powershell = $windows.'\\System32\\WindowsPowerShell\\v1.0\\powershell.exe';
        $process = new Process([
            $powershell, '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass',
            '-File', base_path('scripts/ocr_image.ps1'), '-ImagePath', realpath($path),
            '-Format', $format, '-Language', config('ocr.language', 'pt-PT'),
            '-MaxPages', (string) max(1, min(100, config('ocr.max_pages', 20))),
            '-MaxTiles', (string) max(1, min(32, config('ocr.max_tiles', 16))), '-Json',
        ]);
        $process->setTimeout(max(10, min(600, config('ocr.timeout', 120))));
        $process->run();
        $result = json_decode(ltrim($process->getOutput(), "\xEF\xBB\xBF"), true);
        if (! $process->isSuccessful() || ! is_array($result)) {
            throw new RuntimeException($result['mensagem'] ?? 'Não foi possível executar o OCR nativo do Windows.');
        }

        return $result;
    }
}
