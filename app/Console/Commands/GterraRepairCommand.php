<?php

namespace App\Console\Commands;

use App\Services\InstaladorService;
use Illuminate\Console\Command;
use Throwable;

class GterraRepairCommand extends Command
{
    protected $signature = 'gterra:repair {--force : Confirma a aplicação de migrations pendentes e seeders fundamentais}';

    protected $description = 'Repara uma instalação G.TERRAS sem apagar dados institucionais';

    public function handle(InstaladorService $instalador): int
    {
        if (! $this->option('force') && ! $this->confirm(
            'A recuperação aplica apenas migrations pendentes e dados fundamentais. Nenhuma tabela ou registo operacional será apagado. Continuar?',
            false
        )) {
            $this->warn('Recuperação cancelada.');
            return self::CANCELLED;
        }

        try {
            $this->components->info('Diagnóstico: '.InstaladorService::estado()['detalhe']);
            $instalador->reparar();
            $this->components->info('A estrutura, os dados fundamentais e a conta administrativa foram validados.');
            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error('A recuperação não foi concluída. Consulte os logs Laravel; não foi executada qualquer operação destrutiva.');
            return self::FAILURE;
        }
    }
}
