<?php

namespace App\Console\Commands;

use App\Services\InstaladorService;
use Database\Seeders\ProvinciasMunicipiosSeeder;
use Illuminate\Console\Command;

class GterraSetupCommand extends Command
{
    protected $signature = 'gterra:setup
        {--mode= : central ou node}
        {--code= : Código de três letras da província}
        {--name= : Nome do node}
        {--domain= : Domínio principal}
        {--database= : Base de dados desta instalação}
        {--central-url= : URL pública do núcleo}
        {--dry-run : Mostra a configuração sem alterar o .env}';

    protected $description = 'Configura a identidade e a modalidade desta instalação G.TERRAS';

    public function handle(InstaladorService $instalador): int
    {
        $dados = $instalador->configuracaoActual();
        $mode = $this->option('mode') ?: $this->choice(
            'Papel desta instalação',
            ['central', 'node'],
            $dados['modo']
        );

        if (! in_array($mode, ['central', 'node'], true)) {
            $this->error('O modo deve ser central ou node.');
            return self::INVALID;
        }

        $domain = strtolower((string) ($this->option('domain') ?: $this->ask('Domínio principal', $dados['dominio'])));
        if (! preg_match('/^[A-Za-z0-9.-]+$/', $domain)) {
            $this->error('O domínio indicado não é válido.');
            return self::INVALID;
        }

        $dados['modo'] = $mode;
        $dados['dominio'] = $domain;
        $dados['db_database'] = (string) ($this->option('database') ?: $dados['db_database']);

        if ($mode === 'central') {
            $dados['node_code'] = 'CTR';
            $dados['node_name'] = 'Núcleo Central Nacional';
            $dados['central_url'] = rtrim((string) ($this->option('central-url') ?: $dados['app_url']), '/');
        } else {
            $code = strtoupper((string) ($this->option('code') ?: $this->ask('Código da província (ex.: HUI)')));
            $catalogo = ProvinciasMunicipiosSeeder::catalogo();
            if (! isset($catalogo[$code])) {
                $this->error('O código não existe no catálogo territorial validado.');
                return self::INVALID;
            }

            $dados['node_code'] = $code;
            $dados['node_name'] = (string) ($this->option('name') ?: $this->ask('Nome do node', $catalogo[$code]));
            $dados['central_url'] = rtrim((string) ($this->option('central-url') ?: $this->ask('URL do núcleo central', 'https://'.$domain)), '/');
        }

        $this->table(
            ['Definição', 'Valor'],
            [
                ['Modalidade', $mode],
                ['Domínio', $domain],
                ['Base de dados', $dados['db_database']],
                ['Código do tenant', $mode === 'central' ? 'CTR' : $dados['node_code']],
                ['Nome do node', $dados['node_name']],
                ['URL do núcleo', $dados['central_url']],
            ]
        );

        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        try {
            $instalador->gravarConfiguracao($dados);
            $this->info('Configuração persistida com cópia de segurança do .env.');
            return self::SUCCESS;
        } catch (\Throwable $exception) {
            report($exception);
            $this->error('Não foi possível persistir a configuração. Consulte os logs do Laravel.');
            return self::FAILURE;
        }
    }
}
