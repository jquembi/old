<?php

namespace App\Http\Controllers;

use App\Services\InstaladorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class InstaladorController extends Controller
{
    public function __construct(private InstaladorService $instalador) {}

    public function index()
    {
        return view('instalador.index', [
            'requisitos' => $this->instalador->requisitos(),
            'cumpridos' => $this->instalador->requisitosCumpridos(),
            'sqlite' => extension_loaded('pdo_sqlite'),
            'urlActual' => request()->getSchemeAndHttpHost(),
            'caminhoSqlite' => database_path('database.sqlite'),
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

        return $this->executar(fn () => $this->instalador->testarBase($dados));
    }

    public function passo(Request $request, int $passo): JsonResponse
    {
        @set_time_limit(0);

        return match ($passo) {
            1 => $this->passoConfigurar($request),
            2 => $this->executar(function () {
                $this->instalador->migrar();

                return 'Estrutura da base de dados criada.';
            }),
            3 => $this->executar(function () use ($request) {
                $this->instalador->dadosBase((string) $request->input('modo'));

                return 'Províncias, municípios, nodes, funções e categorias carregados.';
            }),
            4 => $this->passoAdministrador($request),
            default => abort(404),
        };
    }

    private function passoConfigurar(Request $request): JsonResponse
    {
        if (! $this->instalador->requisitosCumpridos()) {
            return response()->json(['ok' => false, 'mensagem' => 'Os requisitos mínimos não estão cumpridos.'], 422);
        }

        $dados = $request->validate($this->regrasBase() + $this->regrasAplicacao());

        return $this->executar(function () use ($dados) {
            $this->instalador->testarBase($dados);
            $this->instalador->gravarConfiguracao($dados);

            return 'Configuração gravada no ficheiro .env.';
        });
    }

    private function passoAdministrador(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'confirmed', Password::min(8)],
            'demo' => ['nullable', 'boolean'],
        ]);

        return $this->executar(function () use ($dados) {
            if (! empty($dados['demo']) && ! app()->isProduction()) {
                $this->instalador->utilizadoresDemonstracao();
            }

            $this->instalador->criarSuperAdmin($dados['admin_name'], $dados['admin_email'], $dados['admin_password']);
            $this->instalador->finalizar();

            return 'Super administrador criado. Instalação concluída.';
        });
    }

    private function executar(callable $accao): JsonResponse
    {
        try {
            return response()->json(['ok' => true, 'mensagem' => $accao()]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['ok' => false, 'mensagem' => $e->getMessage()], 422);
        }
    }

    private function regrasBase(): array
    {
        return [
            'db_connection' => ['required', 'in:sqlite,mysql,mariadb,pgsql'],
            'db_database' => ['required', 'string', 'max:255'],
            'db_host' => ['required_unless:db_connection,sqlite', 'nullable', 'string', 'max:255'],
            'db_port' => ['nullable', 'integer', 'between:1,65535'],
            'db_username' => ['nullable', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function regrasAplicacao(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:100'],
            'app_url' => ['required', 'url', 'max:255'],
            'app_env' => ['required', 'in:producao,local'],
            'modo' => ['required', 'in:central,node'],
            'dominio' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9]([A-Za-z0-9.-]*[A-Za-z0-9])?$/'],
            'node_code' => ['required_if:modo,node', 'nullable', 'regex:/^[A-Za-z]{3}$/'],
            'node_name' => ['required_if:modo,node', 'nullable', 'string', 'max:255'],
            'central_url' => ['required_if:modo,node', 'nullable', 'url', 'max:255'],
        ];
    }
}
