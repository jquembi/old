<?php

namespace App\Services;

use App\Models\Provincia;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\SystemCoreSeeder;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PDO;
use RuntimeException;
use Throwable;

class InstaladorService
{
    private const METADATA_TABLE = 'installation_metadata';

    public static function instalado(): bool
    {
        return in_array(self::estado()['estado'], [
            'completed', 'legacy_installed', 'needs_repair', 'incompatible',
        ], true);
    }

    /**
     * A base de dados é a fonte de verdade. O lock local é apenas auxiliar.
     */
    public static function estado(): array
    {
        try {
            $connection = DB::connection();
            $connection->getPdo();
            $schema = $connection->getSchemaBuilder();
            $tables = collect($schema->getTables())->map(fn ($t) => $t['name'] ?? null)->filter();

            if ($tables->isEmpty()) {
                return ['estado' => 'never_installed', 'detalhe' => 'A base de dados não contém tabelas da aplicação.'];
            }

            if ($schema->hasTable(self::METADATA_TABLE)) {
                $metadata = DB::table(self::METADATA_TABLE)->orderByDesc('id')->first();
                if ($metadata && $metadata->status === 'completed') {
                    return self::integridadeConcluida($metadata)
                        ? ['estado' => 'completed', 'detalhe' => 'A instalação está concluída e foi validada.']
                        : ['estado' => 'needs_repair', 'detalhe' => 'A instalação registada necessita de diagnóstico ou reparação.'];
                }
                if ($metadata && $metadata->status === 'in_progress') {
                    return ['estado' => 'installation_in_progress', 'detalhe' => 'Foi detectada uma instalação em curso que pode ser retomada.'];
                }
                if ($metadata && $metadata->status === 'failed') {
                    return ['estado' => 'partially_installed', 'detalhe' => 'Foi detectada uma instalação parcialmente concluída que necessita de retoma.'];
                }
            }

            if ($schema->hasTable('users') && DB::table('users')->exists()) {
                return ['estado' => 'legacy_installed', 'detalhe' => 'Foi encontrada uma instalação anterior com utilizadores. O instalador público permanece bloqueado.'];
            }

            if ($schema->hasTable('migrations') && DB::table('migrations')->exists()) {
                return ['estado' => 'partially_installed', 'detalhe' => 'Existem migrations aplicadas. A instalação pode ser retomada sem reiniciar a base de dados.'];
            }

            $nonMigrationTables = $tables->reject(fn ($name) => $name === 'migrations');
            if ($nonMigrationTables->isNotEmpty()) {
                return ['estado' => 'incompatible', 'detalhe' => 'Existem tabelas sem histórico de migrations. A instalação automática foi bloqueada para proteger os dados.'];
            }

            return ['estado' => 'partially_installed', 'detalhe' => 'Foi encontrada uma estrutura Laravel inicial sem registos de migrations.'];
        } catch (Throwable) {
            return ['estado' => 'never_installed', 'detalhe' => 'A ligação à base de dados ainda não está disponível. Configure-a e teste-a no assistente.'];
        }
    }

    private static function integridadeConcluida(object $metadata): bool
    {
        try {
            foreach (['users', 'roles', 'permissoes', 'tenants', 'provincias', 'migrations'] as $table) {
                if (! Schema::hasTable($table)) {
                    return false;
                }
            }
            if (! $metadata->admin_email || ! $metadata->tenant_code) {
                return false;
            }

            $user = DB::table('users')->where('email', $metadata->admin_email)->first();
            $roleName = $metadata->deployment_mode === 'node'
                ? Role::ADMIN_SISTEMA_PROVINCIAL
                : Role::SUPER_ADMIN_NACIONAL;
            $role = DB::table('roles')->where('nome', $roleName)->first();
            $pivot = config('rule-manager.table_names.model_has_roles', 'model_has_roles');

            if (! $user || ! $role || ! Schema::hasTable($pivot)) {
                return false;
            }
            if (! DB::table($pivot)->where('role_id', $role->id)
                ->where('model_id', $user->id)->where('model_type', User::class)->exists()) {
                return false;
            }
            if (! DB::table('tenants')->where('code', $metadata->tenant_code)->where('is_active', true)->exists()) {
                return false;
            }
            if ($metadata->deployment_mode === 'node'
                && ! DB::table('provincias')->where('codigo', $metadata->tenant_code)->exists()) {
                return false;
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /** Compatibilidade com integrações antigas; o lock não altera estado. */
    public static function marcarInstalado(): void
    {
        self::escreverMarcadorAuxiliar();
    }

    public static function prepararAmbienteWeb(): void
    {
        $env = base_path('.env');
        if (! is_file($env) && is_file(base_path('.env.example'))) {
            @copy(base_path('.env.example'), $env);
        }
        foreach ([
            storage_path('framework/sessions'),
            storage_path('framework/cache/data'),
            storage_path('app'),
            base_path('bootstrap/cache'),
        ] as $directory) {
            if (! is_dir($directory)) {
                @mkdir($directory, 0755, true);
            }
        }
        if (blank(config('app.key'))) {
            if (is_file($env)) {
                $backup = $env.'.backup-key-'.now()->format('Ymd-His');
                if (@copy($env, $backup)) {
                    @chmod($backup, 0600);
                }
            }
            $key = 'base64:'.base64_encode(random_bytes(32));
            self::escreverEnv(['APP_KEY' => $key]);
            config(['app.key' => $key]);

            // A aplicação pode ter sido publicada com config cache contendo APP_KEY vazio.
            // Remover esse ficheiro é essencial: sem isto, cada novo pedido pode voltar
            // a ler a chave vazia e gerar uma chave diferente, invalidando cookies e sessões.
            $cachedConfig = base_path('bootstrap/cache/config.php');
            if (is_file($cachedConfig) && ! @unlink($cachedConfig)) {
                throw new RuntimeException('A chave da aplicação foi criada, mas não foi possível limpar bootstrap/cache/config.php. Remova a cache de configuração e actualize a página.');
            }
        }

        // Não depender de tabelas de sessão/cache durante a instalação.
        config([
            'session.driver' => 'file',
            'session.cookie' => 'gterras-installer-session',
            'cache.default' => 'file',
            'queue.default' => 'sync',
        ]);
    }

    public function requisitos(): array
    {
        $items = [
            $this->requisito('PHP 8.2 ou superior', PHP_VERSION_ID >= 80200, PHP_VERSION, true),
            $this->requisito('Composer e dependências PHP', is_file(base_path('vendor/autoload.php')), is_file(base_path('vendor/autoload.php')) ? 'Dependências encontradas' : 'Execute composer install', true),
        ];

        foreach (['pdo', 'mbstring', 'openssl', 'fileinfo', 'ctype', 'json', 'tokenizer', 'xml'] as $extension) {
            $items[] = $this->requisito("Extensão {$extension}", extension_loaded($extension), extension_loaded($extension) ? 'Activa' : 'Em falta', true);
        }
        foreach (['curl', 'gd', 'zip', 'exif'] as $extension) {
            $items[] = $this->requisito("Extensão {$extension}", extension_loaded($extension), extension_loaded($extension) ? 'Activa' : 'Opcional; confirme a necessidade dos módulos', false);
        }

        $drivers = PDO::getAvailableDrivers();
        foreach (['sqlite', 'mysql', 'pgsql'] as $driver) {
            $items[] = $this->requisito(
                strtoupper($driver),
                in_array($driver, $drivers, true),
                in_array($driver, $drivers, true) ? 'pdo_'.$driver.' disponível' : 'pdo_'.$driver.' indisponível',
                false
            );
        }
        $items[] = $this->requisito('Pelo menos um driver PDO', count($drivers) > 0, $drivers ? implode(', ', $drivers) : 'Nenhum driver PDO disponível', true);

        foreach ([
            '.env' => is_file(base_path('.env')) ? base_path('.env') : base_path(),
            'storage' => storage_path(),
            'bootstrap/cache' => base_path('bootstrap/cache'),
            'database' => database_path(),
        ] as $label => $path) {
            $writable = is_dir($path) ? is_writable($path) : (is_dir(dirname($path)) && is_writable(dirname($path)));
            $items[] = $this->requisito("Escrita em {$label}", $writable, $writable ? 'Permissão disponível' : 'Sem permissão de escrita', true);
        }

        return $items;
    }

    private function requisito(string $nome, bool $ok, string $detalhe, bool $critico): array
    {
        return compact('nome', 'ok', 'detalhe', 'critico');
    }

    public function requisitosCumpridos(): bool
    {
        return collect($this->requisitos())->where('critico', true)->every(fn ($i) => $i['ok']);
    }

    public function configuracaoActual(): array
    {
        $driver = (string) config('database.default', 'sqlite');
        $connection = config("database.connections.{$driver}", []);

        return [
            'db_connection' => $driver,
            'db_database' => (string) ($connection['database'] ?? database_path('database.sqlite')),
            'db_host' => (string) ($connection['host'] ?? '127.0.0.1'),
            'db_port' => (string) ($connection['port'] ?? ($driver === 'pgsql' ? 5432 : 3306)),
            'db_username' => (string) ($connection['username'] ?? ''),
            'db_password' => (string) ($connection['password'] ?? ''),
            'db_maintenance_database' => 'postgres',
            'db_create_database' => false,
            'app_name' => (string) (config('app.name') === 'Laravel' ? 'G.TERRAS' : config('app.name', 'G.TERRAS')),
            'app_url' => (string) config('app.url', 'http://localhost'),
            'app_env' => app()->isProduction() ? 'producao' : 'local',
            'app_locale' => (string) config('app.locale', 'pt'),
            'app_country' => (string) config('app.country', 'AO'),
            'app_timezone' => (string) config('app.timezone', 'Africa/Luanda'),
            'modo' => (string) config('tenancy.deployment_mode', 'node'),
            'dominio' => (string) config('tenancy.primary_domain', 'gterra.gov.ao'),
            'node_code' => (string) config('tenancy.node.code', 'HUI'),
            'node_name' => (string) config('tenancy.node.name', 'Node Provincial'),
            'central_url' => (string) config('tenancy.sync.central_url', 'https://gterra.gov.ao'),
            'mail_host' => (string) config('mail.mailers.smtp.host', ''),
            'mail_port' => (string) config('mail.mailers.smtp.port', ''),
            'mail_username' => (string) config('mail.mailers.smtp.username', ''),
            'mail_from_address' => (string) config('mail.from.address', ''),
            'mail_from_name' => (string) config('mail.from.name', ''),
        ];
    }

    public function caminhoSqlite(string $path): string
    {
        if ($path === ':memory:') {
            return $path;
        }
        return preg_match('#^([A-Za-z]:[\\\\/]|/)#', $path) ? $path : base_path($path);
    }

    public function testarBase(array $dados): string
    {
        $driver = $dados['db_connection'];
        if ($driver === 'pgsql') {
            throw new RuntimeException('PostgreSQL está temporariamente indisponível no assistente até a compatibilidade de todas as migrations ser validada. Utilize SQLite, MySQL ou MariaDB.');
        }
        $pdoDriver = $driver === 'mariadb' ? 'mysql' : $driver;
        if (! in_array($pdoDriver, PDO::getAvailableDrivers(), true)) {
            throw new RuntimeException("O driver PDO necessário para {$driver} não está instalado.");
        }

        if ($driver === 'sqlite') {
            $path = $this->caminhoSqlite($dados['db_database']);
            if ($path !== ':memory:') {
                $directory = dirname($path);
                if (! is_dir($directory) && ! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
                    throw new RuntimeException('Não foi possível criar o directório da base SQLite.');
                }
                if (! is_file($path) && ! @touch($path)) {
                    throw new RuntimeException('Não foi possível criar o ficheiro SQLite. Verifique as permissões.');
                }
                if (! is_writable($path) || ! is_writable($directory)) {
                    throw new RuntimeException('O ficheiro ou directório SQLite não permite escrita.');
                }
            }
        }

        if (in_array($driver, ['mysql', 'mariadb', 'pgsql'], true) && ! empty($dados['db_create_database'])) {
            $this->criarBaseRemota($dados);
        }

        try {
            $connection = $this->ligar($dados);
            $connection->getPdo();
            $connection->selectOne('SELECT 1 AS installer_probe');
            $table = 'gterra_installer_probe_'.Str::lower(Str::random(8));
            $connection->statement("CREATE TEMPORARY TABLE {$table} (probe_value INTEGER NOT NULL)");
            $connection->insert("INSERT INTO {$table} (probe_value) VALUES (1)");
            $result = $connection->selectOne("SELECT probe_value FROM {$table} LIMIT 1");
            $connection->statement("DROP TABLE {$table}");
            DB::purge('instalador');

            if ((int) ($result->probe_value ?? 0) !== 1) {
                throw new RuntimeException('A base de dados não confirmou a leitura e escrita.');
            }

            return 'Ligação, autenticação, leitura e escrita validadas.';
        } catch (Throwable $exception) {
            DB::purge('instalador');
            if ($exception instanceof RuntimeException && str_contains($exception->getMessage(), 'não')) {
                throw $exception;
            }
            throw new RuntimeException('Não foi possível validar a ligação. Confirme driver, servidor, porta, base, credenciais e permissões. Os detalhes técnicos foram registados no servidor.');
        }
    }

    private function criarBaseRemota(array $dados): void
    {
        $database = (string) $dados['db_database'];
        if (! preg_match('/^[A-Za-z0-9_]+$/', $database)) {
            throw new RuntimeException('O nome da base deve conter apenas letras, números e underscore para criação automática.');
        }

        $driver = $dados['db_connection'];
        $config = $this->configuracaoLigacao($dados);

        if ($driver === 'pgsql') {
            $maintenance = (string) ($dados['db_maintenance_database'] ?? 'postgres');
            if (! preg_match('/^[A-Za-z0-9_]+$/', $maintenance)) {
                throw new RuntimeException('O nome da base de manutenção PostgreSQL é inválido.');
            }
            $config['database'] = $maintenance;
            config(['database.connections.instalador_admin' => $config]);
            DB::purge('instalador_admin');
            $admin = DB::connection('instalador_admin');
            $exists = $admin->selectOne('SELECT 1 FROM pg_database WHERE datname = ?', [$database]);
            if (! $exists) {
                $admin->statement('CREATE DATABASE "'.$database.'"');
            }
            DB::purge('instalador_admin');
            return;
        }

        $config['database'] = null;
        config(['database.connections.instalador_admin' => $config]);
        DB::purge('instalador_admin');
        $admin = DB::connection('instalador_admin');
        $exists = $admin->selectOne('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?', [$database]);
        if (! $exists) {
            // O identificador passou por allow-list estrita antes de ser interpolado.
            $admin->statement('CREATE DATABASE '.$database.' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        }
        DB::purge('instalador_admin');
    }

    private function senhaDaLigacao(array $dados): string
    {
        $provided = (string) ($dados['db_password'] ?? '');
        if ($provided !== '') {
            return $provided;
        }

        $driver = (string) ($dados['db_connection'] ?? '');
        $currentDriver = (string) config('database.default');
        $sameFamily = $driver === $currentDriver
            || (in_array($driver, ['mysql', 'mariadb'], true) && in_array($currentDriver, ['mysql', 'mariadb'], true));
        $current = config("database.connections.{$currentDriver}", []);
        $sameHost = ($dados['db_host'] ?? '127.0.0.1') === ($current['host'] ?? '127.0.0.1');
        $samePort = (string) ($dados['db_port'] ?? '') === (string) ($current['port'] ?? '');
        $sameUser = (string) ($dados['db_username'] ?? '') === (string) ($current['username'] ?? '');

        return $sameFamily && $sameHost && $samePort && $sameUser
            ? (string) ($current['password'] ?? '')
            : '';
    }

    private function configuracaoLigacao(array $dados): array
    {
        $driver = $dados['db_connection'];
        if ($driver === 'sqlite') {
            return [
                'driver' => 'sqlite',
                'database' => $this->caminhoSqlite($dados['db_database']),
                'prefix' => '',
                'foreign_key_constraints' => true,
                'busy_timeout' => 5000,
            ];
        }

        $config = [
            'driver' => $driver,
            'host' => $dados['db_host'] ?? '127.0.0.1',
            'port' => (int) ($dados['db_port'] ?: ($driver === 'pgsql' ? 5432 : 3306)),
            'database' => $dados['db_database'],
            'username' => $dados['db_username'] ?? '',
            'password' => $this->senhaDaLigacao($dados),
            'charset' => $driver === 'pgsql' ? 'utf8' : 'utf8mb4',
            'prefix' => '',
            'prefix_indexes' => true,
            'options' => [PDO::ATTR_TIMEOUT => 5],
        ];
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $config += ['collation' => 'utf8mb4_unicode_ci', 'strict' => true, 'engine' => null];
        } else {
            $config += ['search_path' => 'public', 'sslmode' => $dados['db_sslmode'] ?? 'prefer'];
        }
        return $config;
    }

    private function ligar(array $dados): Connection
    {
        config(['database.connections.instalador' => $this->configuracaoLigacao($dados)]);
        DB::purge('instalador');
        return DB::connection('instalador');
    }

    public function gravarConfiguracao(array $dados): void
    {
        $driver = $dados['db_connection'];
        $database = $driver === 'sqlite' ? $this->caminhoSqlite($dados['db_database']) : $dados['db_database'];
        $production = ($dados['app_env'] ?? 'local') === 'producao';
        $mode = $dados['modo'];
        $domain = strtolower(trim((string) $dados['dominio']));
        $nodeCode = strtoupper((string) ($dados['node_code'] ?? ''));

        $dados['db_password'] = $this->senhaDaLigacao($dados);
        $values = [
            'APP_NAME' => $dados['app_name'],
            'APP_URL' => rtrim($dados['app_url'], '/'),
            'APP_ENV' => $production ? 'production' : 'local',
            'APP_DEBUG' => $production ? 'false' : 'true',
            'APP_LOCALE' => $dados['app_locale'] ?? 'pt',
            'APP_FALLBACK_LOCALE' => 'pt',
            'APP_TIMEZONE' => $dados['app_timezone'] ?? 'Africa/Luanda',
            'APP_COUNTRY' => $dados['app_country'] ?? 'AO',
            'DB_CONNECTION' => $driver,
            'DB_DATABASE' => $database,
            'DB_URL' => '',
            'TENANT_DEPLOYMENT_MODE' => $mode,
            'TENANT_PRIMARY_DOMAIN' => $domain,
            'TENANT_SUBDOMAIN_DOMAIN' => $domain,
            'TENANT_CENTRAL_CONNECTION' => $driver,
            'TENANT_NODE_CONNECTION' => $driver,
            'SESSION_DRIVER' => 'file',
            'SESSION_COOKIE' => 'gterras-session',
            'CACHE_STORE' => 'file',
            'QUEUE_CONNECTION' => 'database',
        ];

        if ($driver !== 'sqlite') {
            $values += [
                'DB_HOST' => $dados['db_host'],
                'DB_PORT' => (string) ($dados['db_port'] ?: ($driver === 'pgsql' ? 5432 : 3306)),
                'DB_USERNAME' => $dados['db_username'] ?? '',
                'DB_PASSWORD' => $dados['db_password'] ?? '',
            ];
            if ($driver === 'pgsql') {
                $values['DB_SSLMODE'] = $dados['db_sslmode'] ?? 'prefer';
            }
        }

        if ($mode === 'central') {
            $values += [
                'TENANT_DEFAULT' => 'CTR',
                'TENANT_NODE_CODE' => '',
                'TENANT_NODE_NAME' => '',
                'TENANT_NODE_DATABASE' => '',
                'TENANT_CENTRAL_URL' => rtrim($dados['app_url'], '/'),
            ];
        } else {
            $values += [
                'TENANT_DEFAULT' => $nodeCode,
                'TENANT_NODE_CODE' => $nodeCode,
                'TENANT_NODE_NAME' => $dados['node_name'],
                'TENANT_NODE_DATABASE' => $database,
                'TENANT_CENTRAL_URL' => rtrim($dados['central_url'], '/'),
            ];
        }

        foreach ([
            'MAIL_HOST' => 'mail_host',
            'MAIL_PORT' => 'mail_port',
            'MAIL_USERNAME' => 'mail_username',
            'MAIL_PASSWORD' => 'mail_password',
            'MAIL_FROM_ADDRESS' => 'mail_from_address',
            'MAIL_FROM_NAME' => 'mail_from_name',
        ] as $key => $field) {
            if (isset($dados[$field]) && $dados[$field] !== '') {
                $values[$key] = (string) $dados[$field];
            }
        }
        if (! empty($dados['mail_host'])) {
            $values['MAIL_MAILER'] = 'smtp';
        }

        $this->backupEnv();
        self::escreverEnv($values);
        $this->aplicarConfiguracaoRuntime($dados);
        Artisan::call('config:clear');
        $this->registarProgresso(1, 'configuration_saved');
    }

    private function aplicarConfiguracaoRuntime(array $dados): void
    {
        $driver = $dados['db_connection'];
        $config = $this->configuracaoLigacao($dados);

        config([
            'app.name' => $dados['app_name'],
            'app.url' => rtrim($dados['app_url'], '/'),
            'app.env' => ($dados['app_env'] ?? 'local') === 'producao' ? 'production' : 'local',
            'app.debug' => ($dados['app_env'] ?? 'local') !== 'producao',
            'app.locale' => $dados['app_locale'] ?? 'pt',
            'app.country' => $dados['app_country'] ?? 'AO',
            'app.timezone' => $dados['app_timezone'] ?? 'Africa/Luanda',
            'database.default' => $driver,
            "database.connections.{$driver}" => $config,
            'tenancy.deployment_mode' => $dados['modo'],
            'tenancy.primary_domain' => strtolower($dados['dominio']),
            'tenancy.default_tenant' => $dados['modo'] === 'central' ? 'CTR' : strtoupper((string) $dados['node_code']),
            'tenancy.node.code' => strtoupper((string) ($dados['node_code'] ?? '')),
            'tenancy.node.name' => $dados['node_name'] ?? '',
            'tenancy.node.database' => $config['database'],
            'tenancy.database.central_connection' => $driver,
            'tenancy.database.node_connection' => $driver,
            'session.driver' => 'file',
            'session.cookie' => 'gterras-session',
            'cache.default' => 'file',
            'queue.default' => 'database',
        ]);
        DB::setDefaultConnection($driver);
        DB::purge($driver);
    }

    public function validarBaseInstalavel(): void
    {
        $connection = DB::connection();
        $connection->getPdo();
        $schema = $connection->getSchemaBuilder();

        if ($schema->hasTable('users') && DB::table('users')->exists()) {
            $metadata = $schema->hasTable(self::METADATA_TABLE)
                ? DB::table(self::METADATA_TABLE)->where('id', 1)->first()
                : null;
            $users = DB::table('users')->get(['id', 'email']);
            $resumingOwnAdmin = $metadata
                && $metadata->status === 'in_progress'
                && $metadata->admin_email
                && $users->count() === 1
                && $users->first()->email === $metadata->admin_email;

            if (! $resumingOwnAdmin) {
                throw new RuntimeException('A base contém utilizadores. A instalação normal foi bloqueada para proteger as contas existentes. Utilize gterra:repair.');
            }
        }

        $tables = collect($schema->getTables())->map(fn ($t) => $t['name'] ?? null)->filter();
        $otherTables = $tables->reject(fn ($name) => $name === 'migrations');
        if ($otherTables->isNotEmpty() && ! $schema->hasTable('migrations')) {
            throw new RuntimeException('A base contém tabelas sem histórico de migrations. Não é seguro iniciar uma instalação normal nesta base.');
        }
        if ($otherTables->isNotEmpty() && $schema->hasTable('migrations') && ! DB::table('migrations')->exists()) {
            throw new RuntimeException('A base contém tabelas, mas o histórico de migrations está vazio. A compatibilidade não pode ser presumida.');
        }
    }

    public function migrar(): void
    {
        $this->validarLigacaoActual();
        $exitCode = Artisan::call('migrate', ['--force' => true]);
        if ($exitCode !== 0 || ! Schema::hasTable('migrations') || ! Schema::hasTable('users')) {
            throw new RuntimeException('Não foi possível aplicar e validar as migrations. Corrija a ligação ou o esquema e tente retomar a instalação.');
        }
        $mode = (string) config('tenancy.deployment_mode', 'node');
        $code = $mode === 'central' ? 'CTR' : strtoupper((string) config('tenancy.node.code'));
        $this->gravarMetadados('in_progress', 2, null, $mode, $code, []);
        $this->registarProgresso(2, 'migrations_completed');
    }

    public function dadosBase(string $modo, bool $vazio = false, ?string $codigo = null, ?string $nome = null): void
    {
        $this->validarLigacaoActual();
        $exitCode = Artisan::call('db:seed', ['--class' => SystemCoreSeeder::class, '--force' => true]);
        if ($exitCode !== 0) {
            throw new RuntimeException('Não foi possível carregar os dados fundamentais. Consulte os logs técnicos e tente novamente.');
        }
        $this->garantirTenant($modo, $codigo, $nome);
        $tenantCode = $modo === 'central' ? 'CTR' : strtoupper((string) $codigo);
        $this->gravarMetadados('in_progress', 3, null, $modo, $tenantCode, []);
        $this->registarProgresso(3, 'core_seeders_completed');
    }

    private function garantirTenant(string $modo, ?string $codigo, ?string $nome): void
    {
        $code = $modo === 'central' ? 'CTR' : strtoupper((string) $codigo);
        if ($modo === 'node' && ! preg_match('/^[A-Z]{3}$/', $code)) {
            throw new RuntimeException('O código da província seleccionada é inválido.');
        }
        if ($modo === 'node') {
            $province = Provincia::withoutGlobalScopes()->where('codigo', $code)->first();
            if (! $province) {
                throw new RuntimeException('A província seleccionada não existe no catálogo territorial carregado.');
            }
            $nome = $province->nome;
        }

        // Criar apenas quando ainda não existe. A recuperação não deve reactivar
        // um tenant suspenso nem substituir nomes ou parâmetros administrativos.
        if (DB::table('tenants')->where('code', $code)->exists()) {
            return;
        }

        $connection = config('database.default');
        DB::table('tenants')->insert([
            'code' => $code,
            'name' => $modo === 'central' ? 'Núcleo Central Nacional' : ($nome ?: $code),
            'type' => $modo === 'central' ? 'central' : 'provincial',
            'database_name' => (string) config("database.connections.{$connection}.database"),
            'database_host' => config("database.connections.{$connection}.host"),
            'database_port' => config("database.connections.{$connection}.port") ? (int) config("database.connections.{$connection}.port") : null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function utilizadoresDemonstracao(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('Os dados de demonstração estão bloqueados em produção.');
        }
        $exitCode = Artisan::call('db:seed', ['--class' => \Database\Seeders\DevelopmentDemoSeeder::class, '--force' => true]);
        if ($exitCode !== 0) {
            throw new RuntimeException('O seeder de demonstração não foi executado.');
        }
    }

    public function registarAdministradorPendente(string $email, string $modo, ?string $codigoProvincia = null): void
    {
        $code = $modo === 'central' ? 'CTR' : strtoupper((string) $codigoProvincia);
        if (! Schema::hasTable(self::METADATA_TABLE)) {
            throw new RuntimeException('A tabela de estado da instalação ainda não existe.');
        }
        $this->gravarMetadados('in_progress', 4, $email, $modo, $code, []);
    }

    public function criarSuperAdmin(string $nome, string $email, string $senha, string $modo = 'central', ?string $codigoProvincia = null): User
    {
        $provincial = $modo === 'node';
        $codigo = $provincial ? strtoupper((string) $codigoProvincia) : 'CTR';
        $provincia = $provincial ? Provincia::withoutGlobalScopes()->where('codigo', $codigo)->first() : null;
        if ($provincial && ! $provincia) {
            throw new RuntimeException('A província seleccionada não foi encontrada.');
        }

        $roleName = $provincial ? Role::ADMIN_SISTEMA_PROVINCIAL : Role::SUPER_ADMIN_NACIONAL;
        $role = Role::query()->where('nome', $roleName)->first();
        if (! $role) {
            throw new RuntimeException('O papel administrativo fundamental não foi carregado. Execute novamente os dados fundamentais.');
        }

        return DB::transaction(function () use ($nome, $email, $senha, $role, $provincia) {
            $existing = User::query()->where('email', $email)->first();
            if ($existing) {
                if (! $existing->roles()->where('roles.id', $role->id)->exists()) {
                    throw new RuntimeException('O email indicado já pertence a uma conta existente. Escolha outro email para o administrador inicial.');
                }
                return $existing;
            }

            $user = User::create([
                'name' => $nome,
                'email' => $email,
                'password' => Hash::make($senha),
                'activo' => true,
                'provincia_id' => $provincia?->id,
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();
            $user->roles()->syncWithoutDetaching([$role->id]);

            if (! $user->roles()->where('roles.id', $role->id)->exists()) {
                throw new RuntimeException('Não foi possível confirmar as permissões da conta administrativa.');
            }
            return $user;
        });
    }

    public function finalizar(string $adminEmail, string $modo = 'central', ?string $codigoProvincia = null): void
    {
        $this->validarLigacaoActual();
        if (! Schema::hasTable(self::METADATA_TABLE)) {
            throw new RuntimeException('A tabela de metadados não existe. Confirme as migrations.');
        }

        $code = $modo === 'central' ? 'CTR' : strtoupper((string) $codigoProvincia);
        $roleName = $modo === 'node' ? Role::ADMIN_SISTEMA_PROVINCIAL : Role::SUPER_ADMIN_NACIONAL;
        $user = User::query()->where('email', $adminEmail)->first();
        $role = Role::query()->where('nome', $roleName)->first();
        if (! $user || ! $role || ! $user->roles()->where('roles.id', $role->id)->exists()) {
            throw new RuntimeException('A conta administrativa e as respectivas permissões não foram validadas.');
        }
        if (! DB::table('tenants')->where('code', $code)->where('is_active', true)->exists()) {
            throw new RuntimeException('O tenant correspondente à modalidade de instalação não foi encontrado.');
        }
        if ($modo === 'node' && ! DB::table('provincias')->where('codigo', $code)->exists()) {
            throw new RuntimeException('A província configurada não foi encontrada.');
        }

        foreach ([
            storage_path('app'),
            storage_path('framework'),
            storage_path('framework/sessions'),
            storage_path('framework/cache/data'),
        ] as $directory) {
            if (! is_dir($directory) && ! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
                throw new RuntimeException('Não foi possível preparar um directório de armazenamento.');
            }
            if (! is_writable($directory)) {
                throw new RuntimeException('Um directório de armazenamento não permite escrita.');
            }
        }

        if (! is_link(public_path('storage')) && ! is_dir(public_path('storage'))) {
            if (Artisan::call('storage:link') !== 0) {
                Log::warning('G.TERRAS: não foi possível criar o link público do storage; verifique os privilégios do servidor.');
            }
        }
        $safeConfig = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'app_env' => config('app.env'),
            'app_locale' => config('app.locale'),
            'app_timezone' => config('app.timezone'),
        ];
        $this->gravarMetadados('completed', 4, $adminEmail, $modo, $code, $safeConfig);
        self::escreverMarcadorAuxiliar();
        $this->registarProgresso(4, 'installation_completed');
    }

    public function reparar(): void
    {
        $this->validarLigacaoActual();
        if (Artisan::call('migrate', ['--force' => true]) !== 0) {
            throw new RuntimeException('A aplicação de migrations pendentes falhou. Nenhum dado foi apagado.');
        }
        if (Artisan::call('db:seed', ['--class' => SystemCoreSeeder::class, '--force' => true]) !== 0) {
            throw new RuntimeException('A reposição dos dados fundamentais falhou. Nenhum dado operacional foi apagado.');
        }

        $mode = (string) config('tenancy.deployment_mode', 'node');
        $code = $mode === 'central' ? 'CTR' : strtoupper((string) config('tenancy.node.code'));
        $name = $mode === 'central' ? 'Núcleo Central Nacional' : (string) config('tenancy.node.name');
        $this->garantirTenant($mode, $code, $name);

        $roleName = $mode === 'node' ? Role::ADMIN_SISTEMA_PROVINCIAL : Role::SUPER_ADMIN_NACIONAL;
        $role = Role::query()->where('nome', $roleName)->first();
        $pivot = config('rule-manager.table_names.model_has_roles', 'model_has_roles');
        $adminId = $role ? DB::table($pivot)->where('role_id', $role->id)->where('model_type', User::class)->value('model_id') : null;
        $user = $adminId ? User::find($adminId) : null;
        if (! $user) {
            throw new RuntimeException('A estrutura foi reparada, mas falta uma conta associada ao papel administrativo correcto. A instalação continua bloqueada para proteger os dados.');
        }
        $this->finalizar($user->email, $mode, $code);
    }

    private function validarLigacaoActual(): void
    {
        try {
            DB::connection()->getPdo();
        } catch (Throwable) {
            throw new RuntimeException('A ligação configurada à base de dados não está disponível. Verifique o .env e as permissões do servidor.');
        }
    }

    private function gravarMetadados(string $status, int $step, ?string $adminEmail, string $mode, string $tenantCode, array $safeConfig = []): void
    {
        DB::table(self::METADATA_TABLE)->updateOrInsert(
            ['id' => 1],
            [
                'status' => $status,
                'completed_step' => $step,
                'app_version' => (string) config('app.version', 'unknown'),
                'db_connection' => (string) config('database.default'),
                'database_name' => (string) config('database.connections.'.config('database.default').'.database'),
                'deployment_mode' => $mode,
                'tenant_code' => $tenantCode,
                'admin_email' => $adminEmail,
                'configuration' => json_encode($safeConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'completed_at' => $status === 'completed' ? now() : null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function registarProgresso(int $step, string $name): void
    {
        $path = storage_path('app/installation-progress.json');
        $progress = [];
        if (is_file($path)) {
            $decoded = json_decode((string) @file_get_contents($path), true);
            if (is_array($decoded)) {
                $progress = $decoded;
            }
        }
        $progress['state'] = $name === 'installation_completed' ? 'completed' : 'in_progress';
        $progress['completed_step'] = max((int) ($progress['completed_step'] ?? 0), $step);
        $progress['last_step'] = $name;
        $progress['updated_at'] = now()->toIso8601String();
        $this->writeAtomic($path, json_encode($progress, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function backupEnv(): void
    {
        $path = base_path('.env');
        if (is_file($path)) {
            $backup = $path.'.backup-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(3));
            if (! @copy($path, $backup)) {
                throw new RuntimeException('Não foi possível criar uma cópia de segurança do .env.');
            }
            @chmod($backup, 0600);
        }
    }

    public static function escreverEnv(array $values): void
    {
        $path = base_path('.env');
        $contents = is_file($path) ? (string) file_get_contents($path) : '';
        foreach ($values as $key => $value) {
            $line = $key.'='.self::citar((string) $value);
            $pattern = '/^'.preg_quote((string) $key, '/').'=.*$/m';
            if (preg_match($pattern, $contents)) {
                $contents = preg_replace_callback($pattern, static fn () => $line, $contents, 1);
            } else {
                $contents = rtrim($contents).PHP_EOL.$line.PHP_EOL;
            }
        }
        $temporary = dirname($path).'/.env.'.bin2hex(random_bytes(6)).'.tmp';
        if (file_put_contents($temporary, $contents, LOCK_EX) === false) {
            @unlink($temporary);
            throw new RuntimeException('Não foi possível escrever o novo ficheiro .env.');
        }
        @chmod($temporary, 0600);
        if (! @rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('Não foi possível substituir o .env de forma atómica.');
        }
    }

    private function writeAtomic(string $path, string $contents): void
    {
        if (! is_dir(dirname($path))) {
            @mkdir(dirname($path), 0755, true);
        }
        $temporary = $path.'.'.bin2hex(random_bytes(5)).'.tmp';
        if (file_put_contents($temporary, $contents, LOCK_EX) === false || ! @rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('Não foi possível guardar o progresso do instalador.');
        }
    }

    private static function escreverMarcadorAuxiliar(): void
    {
        $driver = (string) config('database.default');
        $database = (string) config("database.connections.{$driver}.database");
        $host = (string) config("database.connections.{$driver}.host");
        $marker = [
            'database_fingerprint' => hash('sha256', $driver.'|'.$host.'|'.$database),
            'completed_at' => now()->toIso8601String(),
        ];
        $path = storage_path('app/instalado.lock');
        $temporary = $path.'.'.bin2hex(random_bytes(5)).'.tmp';
        if (! is_dir(dirname($path))) {
            @mkdir(dirname($path), 0755, true);
        }
        if (file_put_contents($temporary, json_encode($marker, JSON_PRETTY_PRINT), LOCK_EX) !== false) {
            @rename($temporary, $path);
        } else {
            @unlink($temporary);
        }
    }

    private static function citar(string $value): string
    {
        if (preg_match('/[\\r\\n\\x00]/', $value)) {
            throw new RuntimeException('Uma configuração contém caracteres de controlo ou quebras de linha e não pode ser gravada no .env.');
        }

        if (! preg_match('/[\s#"\'$\\\\]/', $value)) {
            return $value;
        }
        $escaped = str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value);
        return '"'.$escaped.'"';
    }
}
