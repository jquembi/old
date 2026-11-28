<?php

namespace App\Http\Controllers;

use App\Services\InstaladorService;
use Database\Seeders\ProvinciasMunicipiosSeeder;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class InstaladorController
{
    public function __construct(private readonly InstaladorService $instalador)
    {
    }

    public function index(): View
    {
        $requisitos = $this->instalador->requisitos();
        $catalogo = ProvinciasMunicipiosSeeder::catalogo();
        $databaseDefaults = $this->instalador->configuracaoActual();
        unset($databaseDefaults['db_password']);

        return view('instalador.index', [
            'requisitos' => $requisitos,
            'cumpridos' => $this->instalador->requisitosCumpridos(),
            'sqlite' => in_array('sqlite', \PDO::getAvailableDrivers(), true),
            'drivers' => \PDO::getAvailableDrivers(),
            'databaseDefaults' => $databaseDefaults,
            'provincias' => $catalogo,
            'estadoInstalacao' => InstaladorService::estado(),
            'urlActual' => request()->getSchemeAndHttpHost(),
            'versao' => config('app.version', '12.x'),
            'producao' => app()->isProduction(),
        ]);
    }

    public function token(): JsonResponse
    {
        return response()->json(['token' => csrf_token()]);
    }

    public function testarBase(Request $request): JsonResponse
    {
        $dados = $request->validate($this->regrasBase());
        try {
            $mensagem = $this->instalador->testarBase($dados);
            return response()->json(['ok' => true, 'mensagem' => $mensagem]);
        } catch (RuntimeException $exception) {
            return response()->json(['ok' => false, 'mensagem' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            $reference = (string) str()->uuid();
            Log::error('Falha técnica ao testar a base de dados do instalador.', [
                'reference' => $reference,
                'exception_class' => $exception::class,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
            return response()->json([
                'ok' => false,
                'mensagem' => "Não foi possível testar a ligação. Referência técnica: {$reference}.",
            ], 500);
        }
    }

    public function passo(Request $request, int $passo): JsonResponse
    {
        if ($passo < 1 || $passo > 4) {
            return response()->json(['ok' => false, 'mensagem' => 'Etapa de instalação inválida.'], 404);
        }

        return $this->executar(function () use ($request, $passo) {
            return match ($passo) {
                1 => $this->passoConfigurar($request),
                2 => $this->passoMigrar(),
                3 => $this->passoDadosFundamentais($request),
                4 => $this->passoAdministrador($request),
            };
        });
    }

    private function passoConfigurar(Request $request): string
    {
        $dados = $request->validate(array_merge(
            $this->regrasBase(),
            [
                'app_name' => ['required', 'string', 'max:120'],
                'app_url' => ['required', 'url', 'max:255'],
                'app_env' => ['required', Rule::in(['local', 'producao'])],
                'app_locale' => ['required', 'string', 'max:12'],
                'app_country' => ['nullable', 'string', 'size:2'],
                'app_timezone' => ['required', 'timezone'],
                'modo' => ['required', Rule::in(['central', 'node'])],
                'dominio' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9.-]+$/'],
                'node_code' => [Rule::requiredIf($request->input('modo') === 'node'), 'nullable', 'string', Rule::in(array_keys(ProvinciasMunicipiosSeeder::catalogo()))],
                'node_name' => [Rule::requiredIf($request->input('modo') === 'node'), 'nullable', 'string', 'max:160'],
                'central_url' => [Rule::requiredIf($request->input('modo') === 'node'), 'nullable', 'url', 'max:255'],
                'mail_host' => ['nullable', 'string', 'max:255'],
                'mail_port' => ['nullable', 'integer', 'between:1,65535'],
                'mail_username' => ['nullable', 'string', 'max:255'],
                'mail_password' => ['nullable', 'string', 'max:1024'],
                'mail_from_address' => ['nullable', 'email', 'max:255'],
                'mail_from_name' => ['nullable', 'string', 'max:120'],
            ]
        ));

        $this->instalador->testarBase($dados);
        $this->instalador->gravarConfiguracao($dados);
        $this->instalador->validarBaseInstalavel();

        return 'Configuração e ligação guardadas. A base seleccionada foi validada.';
    }

    private function passoMigrar(): string
    {
        $this->instalador->migrar();
        return 'Estrutura da base de dados criada ou actualizada sem operações destrutivas.';
    }

    private function passoDadosFundamentais(Request $request): string
    {
        $modo = (string) $request->input('modo', config('tenancy.deployment_mode', 'node'));
        $codigo = $modo === 'node' ? strtoupper((string) $request->input('node_code')) : 'CTR';
        $nome = $modo === 'node' ? (string) $request->input('node_name') : 'Núcleo Central Nacional';

        $this->instalador->dadosBase($modo, false, $codigo, $nome);

        return 'Papéis, permissões, catálogo territorial, categorias e tenant inicial garantidos.';
    }

    private function passoAdministrador(Request $request): string
    {
        $dados = $request->validate([
            'admin_name' => ['required', 'string', 'min:3', 'max:160'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => [
                'required',
                'string',
                'confirmed',
                $request->input('app_env') === 'producao'
                    ? Password::min(12)->mixedCase()->letters()->numbers()->symbols()
                    : Password::min(10)->letters()->numbers(),
            ],
            'modo' => ['required', Rule::in(['central', 'node'])],
            'app_env' => ['required', Rule::in(['local', 'producao'])],
            'node_code' => [Rule::requiredIf($request->input('modo') === 'node'), 'nullable', 'string', Rule::in(array_keys(ProvinciasMunicipiosSeeder::catalogo()))],
        ]);

        $mode = $dados['modo'];
        $code = $mode === 'node' ? strtoupper((string) $dados['node_code']) : 'CTR';

        // Regista a conta pretendida antes da criação, para permitir retomar
        // com segurança caso o processo seja interrompido após o commit do utilizador.
        $this->instalador->registarAdministradorPendente($dados['admin_email'], $mode, $code);
        $user = $this->instalador->criarSuperAdmin(
            $dados['admin_name'],
            $dados['admin_email'],
            $dados['admin_password'],
            $mode,
            $code
        );
        $this->instalador->finalizar($user->email, $mode, $code);

        return 'Administrador inicial, permissões, tenant e metadados validados.';
    }

    private function regrasBase(): array
    {
        return [
            'db_connection' => ['required', Rule::in(['sqlite', 'mysql', 'mariadb'])],
            'db_database' => ['required', 'string', 'max:512'],
            'db_host' => ['nullable', 'string', 'max:255'],
            'db_port' => ['nullable', 'integer', 'between:1,65535'],
            'db_username' => ['nullable', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:1024'],
            'db_create_database' => ['sometimes', 'boolean'],
            'db_maintenance_database' => ['nullable', 'string', 'max:64'],
            'db_sslmode' => ['nullable', Rule::in(['disable', 'allow', 'prefer', 'require', 'verify-ca', 'verify-full'])],
        ];
    }

    private function executar(callable $operation): JsonResponse
    {
        try {
            $result = Cache::store('file')->lock('gterras-installer-operation', 90)->block(5, $operation);

            return response()->json([
                'ok' => true,
                'mensagem' => is_string($result) ? $result : 'Etapa concluída e validada.',
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (LockTimeoutException) {
            return response()->json([
                'ok' => false,
                'mensagem' => 'Já existe uma operação de instalação em execução. Aguarde a sua conclusão e tente novamente.',
            ], 409);
        } catch (RuntimeException $exception) {
            return response()->json(['ok' => false, 'mensagem' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            $reference = (string) str()->uuid();
            Log::error('Falha técnica durante uma etapa do instalador G.TERRAS.', [
                'reference' => $reference,
                'exception_class' => $exception::class,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
            return response()->json([
                'ok' => false,
                'mensagem' => "A etapa não foi concluída. Consulte o suporte com a referência {$reference}.",
            ], 500);
        }
    }
}
