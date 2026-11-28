<?php

namespace App\Console\Commands;

use App\Services\InstaladorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\Process\Process;
use Throwable;

class GterraInstallCommand extends Command
{
    protected $signature = 'gterra:install
        {--admin-name= : Nome do administrador inicial}
        {--admin-email= : Email institucional do administrador}
        {--admin-password= : Palavra-passe do administrador}
        {--demo : Carrega dados demonstrativos (apenas desenvolvimento local e núcleo central)}
        {--configure : Abre a configuração central/node antes de instalar}
        {--skip-build : Não instala dependências Node nem compila os assets}
        {--force : Confirma a instalação inicial em ambiente de produção}';

    protected $description = 'Instala o G.TERRAS através do mesmo serviço usado pelo assistente visual';

    public function handle(InstaladorService $instalador): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Ambiente de produção detectado. Use --force para confirmar a primeira instalação.');
            return self::FAILURE;
        }

        $estado = InstaladorService::estado()['estado'];
        if (in_array($estado, ['completed', 'legacy_installed', 'needs_repair', 'incompatible'], true)) {
            $this->error('Esta base já contém uma instalação ou dados protegidos. --force não permite reinstalar nem apagar dados. Utilize gterra:repair.');
            return self::FAILURE;
        }

        try {
            $instalador->prepararAmbienteWeb();

            if ($this->option('configure')) {
                if ($this->call('gterra:setup') !== self::SUCCESS) {
                    return self::FAILURE;
                }
            }

            $dados = $instalador->configuracaoActual();
            $instalador->testarBase($dados);
            $instalador->validarBaseInstalavel();
            $this->components->task('Validar ligação e permissões da base', fn () => true);

            $instalador->migrar();
            $this->components->task('Aplicar migrations pendentes', fn () => true);

            $mode = $dados['modo'];
            $code = $mode === 'central' ? 'CTR' : strtoupper($dados['node_code']);
            $name = $mode === 'central' ? 'Núcleo Central Nacional' : $dados['node_name'];
            $instalador->dadosBase($mode, false, $code, $name);
            $this->components->task('Carregar dados fundamentais e tenant', fn () => true);

            if ($this->option('demo')) {
                if (app()->environment('production') || $mode !== 'central') {
                    $this->error('Dados demonstrativos só podem ser carregados em desenvolvimento local no núcleo central.');
                    return self::FAILURE;
                }
                $instalador->utilizadoresDemonstracao();
            }

            $name = (string) ($this->option('admin-name') ?: $this->ask('Nome completo do administrador'));
            $email = (string) ($this->option('admin-email') ?: $this->ask('Email institucional do administrador'));
            $password = (string) ($this->option('admin-password') ?: $this->secret('Palavra-passe do administrador'));

            $passwordRule = app()->isProduction()
                ? Password::min(12)->mixedCase()->letters()->numbers()->symbols()
                : Password::min(10)->letters()->numbers();
            $validator = Validator::make(
                ['name' => $name, 'email' => $email, 'password' => $password],
                ['name' => ['required', 'string', 'min:3', 'max:160'], 'email' => ['required', 'email', 'max:255'], 'password' => ['required', $passwordRule]],
            );
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $this->error($error);
                }
                return self::INVALID;
            }

            $admin = $instalador->criarSuperAdmin($name, $email, $password, $mode, $code);
            $instalador->finalizar($admin->email, $mode, $code);
            $this->components->task('Criar e validar o administrador inicial', fn () => true);

            if (! $this->option('skip-build')) {
                $this->warn('A compilação dos assets é opcional e não impede o acesso ao instalador ou à aplicação.');
                foreach ([['npm', 'install'], ['npm', 'run', 'build']] as $command) {
                    $process = new Process($command, base_path());
                    $process->setTimeout(600);
                    $process->run();
                    if (! $process->isSuccessful()) {
                        $this->warn('Não foi possível executar '.implode(' ', $command).'. Compile os assets posteriormente.');
                        break;
                    }
                }
            }

            $this->newLine();
            $this->components->info('Instalação concluída e validada. Aceda ao /login.');
            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error('A instalação não foi concluída. Consulte os logs Laravel; os dados existentes foram preservados.');
            return self::FAILURE;
        }
    }
}
