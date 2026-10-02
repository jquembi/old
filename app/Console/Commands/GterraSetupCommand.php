<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class GterraSetupCommand extends Command
{
    protected $signature = 'gterra:setup
        {--mode= : central ou node}
        {--code= : Código de três letras do node}
        {--name= : Nome do node}
        {--domain= : Domínio principal, por exemplo gterra.gov.ao}
        {--database= : Base de dados desta instalação}
        {--central-url= : URL pública do núcleo}
        {--dry-run : Mostra a configuração sem alterar o .env}';

    protected $description = 'Configura esta instalação como núcleo nacional ou node provincial';

    public function handle(): int
    {
        $mode = $this->option('mode') ?: $this->choice('Papel desta instalação', ['central', 'node'], config('tenancy.deployment_mode'));

        if (! in_array($mode, ['central', 'node'], true)) {
            $this->error('O modo deve ser central ou node.');

            return self::INVALID;
        }

        $domain = strtolower((string) ($this->option('domain') ?: $this->ask('Domínio principal', config('tenancy.primary_domain'))));
        $database = (string) ($this->option('database') ?: $this->ask('Base de dados local', config('database.connections.'.config('database.default').'.database')));
        $values = [
            'TENANT_DEPLOYMENT_MODE' => $mode,
            'TENANT_PRIMARY_DOMAIN' => $domain,
            'TENANT_SUBDOMAIN_DOMAIN' => $domain,
            // A ligação base desta instalação também precisa apontar para a
            // base escolhida, quer ela seja o núcleo quer um node.
            'DB_DATABASE' => $database,
        ];

        if ($mode === 'central') {
            $values['TENANT_DEFAULT'] = 'CTR';
            $values['TENANT_CENTRAL_URL'] = 'https://'.$domain;
        } else {
            $code = strtoupper((string) ($this->option('code') ?: $this->ask('Código do node (ex.: HUI)')));
            $name = (string) ($this->option('name') ?: $this->ask('Nome do node'));

            if (! preg_match('/^[A-Z]{3}$/', $code) || $name === '') {
                $this->error('O código do node deve ter exatamente três letras e o nome é obrigatório.');

                return self::INVALID;
            }

            $values += [
                'TENANT_DEFAULT' => $code,
                'TENANT_NODE_CODE' => $code,
                'TENANT_NODE_NAME' => $name,
                'TENANT_NODE_DATABASE' => $database,
                'TENANT_CENTRAL_URL' => rtrim((string) ($this->option('central-url') ?: $this->ask('URL do núcleo', 'https://'.$domain)), '/'),
            ];
        }

        $this->table(['Variável', 'Valor'], collect($values)->map(fn ($value, $key) => [$key, $value])->values());

        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        $this->writeEnvironment($values);

        if ($mode === 'central' && \Schema::hasTable('tenants')) {
            Tenant::updateOrCreate(['code' => 'CTR'], [
                'name' => 'Núcleo Central Nacional',
                'type' => Tenant::TYPE_CENTRAL,
                'database_name' => $database,
                'is_active' => true,
            ]);
        }

        $this->info('Configuração gravada. Execute: php artisan config:clear && php artisan migrate --force');

        return self::SUCCESS;
    }

    private function writeEnvironment(array $values): void
    {
        $path = base_path('.env');
        $contents = file_exists($path) ? file_get_contents($path) : '';

        foreach ($values as $key => $value) {
            $line = $key.'='.$this->quote((string) $value);
            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';
            $contents = preg_match($pattern, $contents)
                ? preg_replace($pattern, $line, $contents)
                : rtrim($contents).PHP_EOL.$line.PHP_EOL;
        }

        file_put_contents($path, $contents);
    }

    private function quote(string $value): string
    {
        return preg_match('/\s|#/', $value) ? '"'.addcslashes($value, '"\\').'"' : $value;
    }
}
