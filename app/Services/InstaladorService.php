<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\CategoriaInstrumentosSeeder;
use Database\Seeders\NodesSeeder;
use Database\Seeders\ProvinciasMunicipiosSeeder;
use Database\Seeders\RolesPermissoesSeederV2;
use Database\Seeders\UsuariosIniciaisSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class InstaladorService
{
    private static bool $instalado = false;

    /**
     * A instalação está concluída quando existe o ficheiro de bloqueio ou já há
     * utilizadores. Em produção, uma base inacessível é tratada como instalada
     * para que o assistente nunca fique exposto por uma falha de ligação.
     */
    public static function instalado(): bool
    {
        if (self::$instalado) {
            return true;
        }

        $teste = app()->runningUnitTests();

        if (! $teste && is_file(self::ficheiroBloqueio())) {
            return self::$instalado = true;
        }

        try {
            $existe = Schema::hasTable('users') && DB::table('users')->exists();
        } catch (\Throwable) {
            return app()->isProduction();
        }

        if ($existe) {
            self::marcarInstalado();
        }

        return $existe;
    }

    public static function marcarInstalado(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        @file_put_contents(self::ficheiroBloqueio(), now()->toIso8601String());
        self::$instalado = true;
    }

    private static function ficheiroBloqueio(): string
    {
        return storage_path('app/instalado.lock');
    }

    /**
     * Prepara um pedido web numa instalação ainda por concluir: garante .env e
     * chave, e evita drivers que dependem de tabelas ainda inexistentes.
     */
    public static function prepararAmbienteWeb(): void
    {
        $env = base_path('.env');

        if (! is_file($env) && is_file(base_path('.env.example'))) {
            @copy(base_path('.env.example'), $env);
        }

        if (blank(config('app.key'))) {
            $chave = 'base64:'.base64_encode(random_bytes(32));
            self::escreverEnv(['APP_KEY' => $chave]);
            config(['app.key' => $chave]);
        }

        config([
            'session.driver' => 'file',
            'cache.default' => 'file',
            'queue.default' => 'sync',
        ]);
    }

    public function requisitos(): array
    {
        $extensoes = ['pdo', 'mbstring', 'openssl', 'fileinfo', 'ctype', 'json', 'tokenizer', 'xml', 'curl', 'gd', 'zip'];

        $itens = [[
            'nome' => 'PHP 8.2 ou superior',
            'ok' => PHP_VERSION_ID >= 80200,
            'detalhe' => PHP_VERSION,
            'critico' => true,
        ]];

        foreach ($extensoes as $ext) {
            $itens[] = [
                'nome' => "Extensão {$ext}",
                'ok' => extension_loaded($ext),
                'detalhe' => extension_loaded($ext) ? 'activa' : 'em falta',
                'critico' => ! in_array($ext, ['gd', 'zip'], true),
            ];
        }

        $itens[] = [
            'nome' => 'Driver de base de dados (SQLite, MySQL ou PostgreSQL)',
            'ok' => (bool) array_intersect(['pdo_sqlite', 'pdo_mysql', 'pdo_pgsql'], get_loaded_extensions()),
            'detalhe' => implode(', ', array_intersect(['pdo_sqlite', 'pdo_mysql', 'pdo_pgsql'], get_loaded_extensions())) ?: 'nenhum',
            'critico' => true,
        ];

        foreach ([
            '.env' => is_file(base_path('.env')) ? base_path('.env') : base_path(),
            'storage' => storage_path(),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ] as $nome => $caminho) {
            $itens[] = [
                'nome' => "Escrita em {$nome}",
                'ok' => is_writable($caminho),
                'detalhe' => is_writable($caminho) ? 'permitida' : 'sem permissão',
                'critico' => true,
            ];
        }

        return $itens;
    }

    public function requisitosCumpridos(): bool
    {
        return collect($this->requisitos())->where('critico', true)->every(fn ($i) => $i['ok']);
    }

    public function caminhoSqlite(string $caminho): string
    {
        $absoluto = preg_match('#^([A-Za-z]:[\\\\/]|/)#', $caminho) === 1;

        return $absoluto ? $caminho : base_path($caminho);
    }

    /**
     * Testa a ligação com os dados fornecidos. Em MySQL/MariaDB tenta criar a
     * base de dados quando ela ainda não existe.
     */
    public function testarBase(array $d): string
    {
        $driver = $d['db_connection'];

        if ($driver === 'sqlite') {
            $ficheiro = $this->caminhoSqlite($d['db_database']);
            @mkdir(dirname($ficheiro), 0755, true);

            if (! is_file($ficheiro) && @touch($ficheiro) === false) {
                throw new \RuntimeException("Não foi possível criar o ficheiro {$ficheiro}.");
            }
        }

        try {
            $this->ligar($d)->getPdo();
        } catch (\Throwable $e) {
            if (! in_array($driver, ['mysql', 'mariadb'], true) || ! str_contains($e->getMessage(), '1049')) {
                throw $e;
            }

            $this->ligar($d, semBase: true)->statement('CREATE DATABASE IF NOT EXISTS `'.str_replace('`', '', $d['db_database']).'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $this->ligar($d)->getPdo();

            return 'Ligação estabelecida. A base de dados foi criada.';
        }

        return 'Ligação estabelecida com sucesso.';
    }

    private function ligar(array $d, bool $semBase = false): \Illuminate\Database\Connection
    {
        $driver = $d['db_connection'];

        $config = $driver === 'sqlite'
            ? ['driver' => 'sqlite', 'database' => $this->caminhoSqlite($d['db_database']), 'prefix' => '', 'foreign_key_constraints' => true]
            : [
                'driver' => $driver,
                'host' => $d['db_host'],
                'port' => $d['db_port'] ?: ($driver === 'pgsql' ? 5432 : 3306),
                'database' => $semBase ? null : $d['db_database'],
                'username' => $d['db_username'] ?? '',
                'password' => $d['db_password'] ?? '',
                'charset' => $driver === 'pgsql' ? 'utf8' : 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'schema' => 'public',
                'options' => [\PDO::ATTR_TIMEOUT => 5],
            ];

        config(['database.connections.instalador' => $config]);
        DB::purge('instalador');

        return DB::connection('instalador');
    }

    public function gravarConfiguracao(array $d): void
    {
        $driver = $d['db_connection'];
        $producao = $d['app_env'] === 'producao';

        $valores = [
            'APP_NAME' => $d['app_name'],
            'APP_URL' => rtrim($d['app_url'], '/'),
            'APP_ENV' => $producao ? 'production' : 'local',
            'APP_DEBUG' => $producao ? 'false' : 'true',
            'DB_CONNECTION' => $driver,
        ];

        if ($driver === 'sqlite') {
            $valores['DB_DATABASE'] = $this->caminhoSqlite($d['db_database']);
        } else {
            $valores += [
                'DB_HOST' => $d['db_host'],
                'DB_PORT' => (string) ($d['db_port'] ?: ($driver === 'pgsql' ? 5432 : 3306)),
                'DB_DATABASE' => $d['db_database'],
                'DB_USERNAME' => $d['db_username'] ?? '',
                'DB_PASSWORD' => $d['db_password'] ?? '',
            ];
        }

        $dominio = strtolower($d['dominio']);
        $valores += [
            'TENANT_DEPLOYMENT_MODE' => $d['modo'],
            'TENANT_PRIMARY_DOMAIN' => $dominio,
            'TENANT_SUBDOMAIN_DOMAIN' => $dominio,
        ];

        if ($d['modo'] === 'central') {
            $valores += ['TENANT_DEFAULT' => 'CTR', 'TENANT_CENTRAL_URL' => 'https://'.$dominio];
        } else {
            $codigo = strtoupper($d['node_code']);
            $valores += [
                'TENANT_DEFAULT' => $codigo,
                'TENANT_NODE_CODE' => $codigo,
                'TENANT_NODE_NAME' => $d['node_name'],
                'TENANT_NODE_DATABASE' => $driver === 'sqlite' ? $this->caminhoSqlite($d['db_database']) : $d['db_database'],
                'TENANT_CENTRAL_URL' => rtrim($d['central_url'], '/'),
            ];
        }

        self::escreverEnv($valores);
        Artisan::call('config:clear');
    }

    public function migrar(): void
    {
        Artisan::call('migrate', ['--force' => true]);
    }

    public function dadosBase(string $modo): void
    {
        foreach ([ProvinciasMunicipiosSeeder::class, NodesSeeder::class, RolesPermissoesSeederV2::class, CategoriaInstrumentosSeeder::class] as $seeder) {
            Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);
        }

        if ($modo === 'central' && Schema::hasTable('tenants')) {
            Tenant::updateOrCreate(['code' => 'CTR'], [
                'name' => 'Núcleo Central Nacional',
                'type' => Tenant::TYPE_CENTRAL,
                'database_name' => config('database.connections.'.config('database.default').'.database'),
                'is_active' => true,
            ]);
        }
    }

    public function utilizadoresDemonstracao(): void
    {
        Artisan::call('db:seed', ['--class' => UsuariosIniciaisSeeder::class, '--force' => true]);
    }

    public function criarSuperAdmin(string $nome, string $email, string $senha): User
    {
        $role = Role::firstOrCreate(
            ['nome' => Role::SUPER_ADMIN_NACIONAL],
            ['descricao' => 'Super Administrador Nacional - Visão geral completa do sistema', 'nivel' => Role::NIVEL_NACIONAL, 'grupo' => 'INOTU Nacional'],
        );

        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $nome, 'password' => Hash::make($senha), 'email_verified_at' => now(), 'activo' => true],
        );
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    public function finalizar(): void
    {
        try {
            Artisan::call('storage:link', ['--force' => true]);
        } catch (\Throwable) {
            // Em alguns sistemas a criação de links simbólicos exige privilégios.
        }

        Artisan::call('optimize:clear');
        self::marcarInstalado();
    }

    public static function escreverEnv(array $valores): void
    {
        $caminho = base_path('.env');
        $conteudo = is_file($caminho) ? file_get_contents($caminho) : '';

        foreach ($valores as $chave => $valor) {
            $linha = $chave.'='.self::citar((string) $valor);
            $padrao = '/^'.preg_quote($chave, '/').'=.*$/m';
            $conteudo = preg_match($padrao, $conteudo)
                ? preg_replace($padrao, addcslashes($linha, '\\$'), $conteudo)
                : rtrim($conteudo).PHP_EOL.$linha.PHP_EOL;
        }

        file_put_contents($caminho, $conteudo);
    }

    private static function citar(string $valor): string
    {
        return preg_match('/[\s#"\'$\\\\]/', $valor)
            ? '"'.addcslashes($valor, '"\\$').'"'
            : $valor;
    }
}
