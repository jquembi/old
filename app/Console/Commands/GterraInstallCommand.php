<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\CategoriaInstrumentosSeeder;
use Database\Seeders\NodesSeeder;
use Database\Seeders\ProvinciasMunicipiosSeeder;
use Database\Seeders\RolesPermissoesSeederV2;
use Database\Seeders\UsuariosIniciaisSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\Process\Process;

class GterraInstallCommand extends Command
{
    protected $signature = 'gterra:install
        {--admin-name= : Nome do super administrador}
        {--admin-email= : Email do super administrador}
        {--admin-password= : Palavra-passe do super administrador (mín. 8 caracteres)}
        {--demo : Carrega também os utilizadores de demonstração (NÃO usar em produção)}
        {--configure : Executa o assistente gterra:setup (central/node) antes de migrar}
        {--skip-build : Não instala dependências Node nem compila os assets}
        {--force : Permite executar em produção e reinstalar sobre dados existentes}';

    protected $description = 'Instala o projecto: ambiente, chave, migrations, dados base, super administrador e assets';

    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Ambiente de produção detectado. Use --force para confirmar.');

            return self::FAILURE;
        }

        $this->components->info('Instalação do GTerra / INOTU');

        $this->prepararAmbiente();

        if ($this->option('configure')) {
            $this->call('gterra:setup');
            $this->call('config:clear');
        }

        $this->criarBaseSqlite();

        $this->components->task('Migrations', fn () => $this->callSilent('migrate', ['--force' => true]) === 0);

        if (! Schema::hasTable('users')) {
            $this->error('As migrations não foram aplicadas. Verifique a ligação à base de dados no .env.');

            return self::FAILURE;
        }

        $this->components->task('Dados base (províncias, nodes, funções, categorias)', function () {
            foreach ([ProvinciasMunicipiosSeeder::class, NodesSeeder::class, RolesPermissoesSeederV2::class, CategoriaInstrumentosSeeder::class] as $seeder) {
                $this->callSilent('db:seed', ['--class' => $seeder, '--force' => true]);
            }

            return true;
        });

        if ($this->option('demo')) {
            $this->components->task('Utilizadores de demonstração', fn () => $this->callSilent('db:seed', ['--class' => UsuariosIniciaisSeeder::class, '--force' => true]) === 0);
            $this->warn('Contas de demonstração criadas com palavras-passe conhecidas. Não use em produção.');
        } elseif (! $this->criarSuperAdmin()) {
            return self::FAILURE;
        }

        $this->components->task('Link do storage', fn () => $this->callSilent('storage:link', ['--force' => true]) === 0);

        if (! $this->option('skip-build')) {
            $this->components->task('Dependências Node', fn () => $this->executar('npm install'));
            $this->components->task('Compilação dos assets', fn () => $this->executar('npm run build'));
        }

        $this->callSilent('optimize:clear');

        $this->newLine();
        $this->components->info('Instalação concluída. Inicie com: php artisan serve');

        return self::SUCCESS;
    }

    private function prepararAmbiente(): void
    {
        $env = base_path('.env');

        if (! file_exists($env) && file_exists(base_path('.env.example'))) {
            copy(base_path('.env.example'), $env);
            $this->components->twoColumnDetail('.env', 'criado a partir de .env.example');
        }

        if (blank(config('app.key'))) {
            $this->callSilent('key:generate', ['--force' => true]);
            $this->call('config:clear');
            $this->components->twoColumnDetail('APP_KEY', 'gerada');
        }
    }

    private function criarBaseSqlite(): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        $path = config('database.connections.sqlite.database');

        if ($path && $path !== ':memory:' && ! file_exists($path)) {
            @mkdir(dirname($path), 0755, true);
            touch($path);
            $this->components->twoColumnDetail('SQLite', 'ficheiro criado');
        }
    }

    private function criarSuperAdmin(): bool
    {
        $nome = $this->option('admin-name') ?: $this->ask('Nome do super administrador', 'Super Administrador Nacional');
        $email = $this->option('admin-email') ?: $this->ask('Email do super administrador');
        $senha = $this->option('admin-password') ?: $this->secret('Palavra-passe (mín. 8 caracteres)');

        $validator = Validator::make(
            ['name' => $nome, 'email' => $email, 'password' => $senha],
            ['name' => 'required|string|max:255', 'email' => 'required|email|max:255', 'password' => ['required', Password::min(8)]],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $erro) {
                $this->error($erro);
            }

            return false;
        }

        $role = Role::firstOrCreate(
            ['nome' => Role::SUPER_ADMIN_NACIONAL],
            ['descricao' => 'Super Administrador Nacional - Visão geral completa do sistema', 'nivel' => Role::NIVEL_NACIONAL, 'grupo' => 'INOTU Nacional'],
        );

        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $nome, 'password' => Hash::make($senha), 'email_verified_at' => now(), 'activo' => true],
        );
        $user->roles()->syncWithoutDetaching([$role->id]);

        $this->components->twoColumnDetail('Super administrador', $email);

        return true;
    }

    private function executar(string $comando): bool
    {
        $processo = Process::fromShellCommandline($comando, base_path(), null, null, 900);

        try {
            $processo->run();
        } catch (\Throwable) {
            return false;
        }

        return $processo->isSuccessful();
    }
}
