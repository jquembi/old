<?php

use App\Services\WordToPdfService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('inotu:check-libreoffice', function (WordToPdfService $service) {
    $binary = $service->binarioDisponivel();

    if (! $binary) {
        $this->error('LibreOffice/soffice nao encontrado.');
        $this->line('Instale o LibreOffice no servidor e, se necessario, defina LIBREOFFICE_BINARY no .env.');
        $this->line('Exemplo Linux: LIBREOFFICE_BINARY=/usr/bin/soffice');
        $this->line('Exemplo Windows: LIBREOFFICE_BINARY="C:\Program Files\LibreOffice\program\soffice.exe"');

        return 1;
    }

    $this->info('LibreOffice encontrado: ' . $binary);

    return 0;
})->purpose('Verifica se o LibreOffice esta disponivel para converter memorias descritivas Word em PDF.');
