<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SetTenant
{
    public function handle(Request $request, Closure $next, string $validateUser = 'false')
    {
        $this->useDeploymentConnection();

        $tenant = $this->resolveTenant($request);
        
        if (!$tenant) {
            $tenant = $this->isNodeDeployment()
                ? $this->localNodeTenant()
                : Tenant::central()->first();
            
            if (!$tenant) {
                abort(404, 'Tenant não encontrado');
            }
        }

        // Verificar se o tenant está ativo
        if (!$tenant->is_active) {
            abort(403, 'Tenant inativo');
        }

        if ($validateUser === 'validate' && auth()->check() && ! $this->userCanAccessTenant(auth()->user(), $tenant)) {
            abort(403, 'O utilizador não tem acesso a este tenant');
        }

        // Set tenant connection
        $this->setTenantConnection($tenant);
        
        // Store tenant in context
        app()->instance('current.tenant', $tenant);
        
        // Store tenant ID in request for easy access
        $request->attributes->set('tenant', $tenant);
        
        try {
            return $next($request);
        } finally {
            // A conexão tenant só vale durante esta requisição. Restaurar a
            // conexão central evita vazamento de contexto entre jobs/testes/Octane.
            $this->useDeploymentConnection();
        }
    }

    private function resolveTenant(Request $request): ?Tenant
    {
        if ($this->isNodeDeployment()) {
            return $this->resolveLocalNode($request);
        }

        $methods = config('tenancy.resolution_methods', []);

        // 1. Verificar subdomínio (huila.gterra.ao, central.gterra.ao)
        if (in_array('subdomain', $methods, true) && ($subdomain = $this->getSubdomain($request))) {
            $tenant = Tenant::where('code', strtoupper($subdomain))->first();
            if ($tenant) {
                return $tenant;
            }
        }

        // 2. Verificar header X-Tenant
        $header = config('tenancy.header_name', 'X-Tenant');
        if (in_array('header', $methods, true) && $request->hasHeader($header)) {
            $tenant = Tenant::where('code', strtoupper($request->header($header)))->first();
            if ($tenant) {
                return $tenant;
            }
        }

        // 3. Verificar parâmetro tenant na query string
        $parameter = config('tenancy.query_parameter', 'tenant');
        if (in_array('query', $methods, true) && $request->has($parameter)) {
            $tenant = Tenant::where('code', strtoupper($request->query($parameter)))->first();
            if ($tenant) {
                return $tenant;
            }
        }

        // 4. Verificar utilizador autenticado
        if (in_array('user', $methods, true) && auth()->check()) {
            $user = auth()->user();
            
            // Se o utilizador tem provincia_id, usar o tenant provincial correspondente
            if ($user->provincia_id) {
                $tenant = Tenant::where('type', 'provincial')
                    ->where('code', $user->provincia->codigo ?? null)
                    ->first();
                
                if ($tenant) {
                    return $tenant;
                }
            }
            
            // Se o utilizador é nível nacional, usar tenant central
            if ($user->isNacional()) {
                return Tenant::central()->first();
            }
        }

        // 5. Verificar configuração padrão
        $defaultTenantCode = config('tenancy.default_tenant');
        if ($defaultTenantCode) {
            $tenant = Tenant::where('code', $defaultTenantCode)->first();
            if ($tenant) {
                return $tenant;
            }
        }

        return null;
    }

    private function getSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        $domain = config('tenancy.subdomain_domain');

        if ($domain && str_ends_with($host, '.'.$domain)) {
            $subdomain = substr($host, 0, -strlen($domain) - 1);

            if ($subdomain !== '' && ! str_contains($subdomain, '.') && $subdomain !== 'www') {
                return $subdomain;
            }
        }
        
        return null;
    }

    private function resolveLocalNode(Request $request): ?Tenant
    {
        $code = strtoupper((string) config('tenancy.node.code'));

        if ($code === '') {
            throw new \RuntimeException('TENANT_NODE_CODE é obrigatório em instalações de node.');
        }

        $host = $request->getHost();
        $expectedHost = strtolower($code.'.'.config('tenancy.primary_domain'));

        if (app()->environment('production') && strtolower($host) !== $expectedHost) {
            abort(404, 'Este node não responde por este domínio.');
        }

        return $this->localNodeTenant();
    }

    private function localNodeTenant(): Tenant
    {
        return new Tenant([
            'id' => config('tenancy.node.id'),
            'code' => strtoupper((string) config('tenancy.node.code')),
            'name' => config('tenancy.node.name'),
            'type' => Tenant::TYPE_PROVINCIAL,
            'database_name' => config('tenancy.node.database'),
            'is_active' => config('tenancy.node.active'),
            'sync_status' => Tenant::SYNC_ONLINE,
        ]);
    }

    private function isNodeDeployment(): bool
    {
        return config('tenancy.deployment_mode') === 'node';
    }

    private function userCanAccessTenant($user, Tenant $tenant): bool
    {
        $user->loadMissing('provincia');

        if ($user->isNacional() || $user->temPermissao('nodes.acessar')) {
            return true;
        }

        return $tenant->isProvincial()
            && $user->provincia !== null
            && strtoupper($user->provincia->codigo) === $tenant->code;
    }

    private function useDeploymentConnection(): void
    {
        $connectionName = $this->isNodeDeployment()
            ? config('tenancy.database.node_connection')
            : config('tenancy.database.central_connection');
        $connection = config("database.connections.{$connectionName}");

        if (! is_array($connection)) {
            throw new \RuntimeException("Ligação [{$connectionName}] não configurada.");
        }

        Config::set('database.default', $connectionName);
    }

    private function setTenantConnection(Tenant $tenant): void
    {
        // O tenant central é sempre explícito; isto evita reutilizar uma ligação
        // provincial em processos persistentes (queue workers/Octane).
        if ($tenant->isCentral()) {
            Config::set('database.default', config('tenancy.database.central_connection'));
            return;
        }

        // O núcleo trata payloads provinciais na sua própria base. Ele nunca
        // abre uma conexão com um node, mesmo durante sincronização.
        if (! $this->isNodeDeployment()) {
            Config::set('database.default', config('tenancy.database.central_connection'));
            return;
        }

        // Configurar conexão específica para o tenant
        $connection = $tenant->getDatabaseConnection();
        
        Config::set('database.connections.tenant', $connection);
        DB::purge('tenant');
        Config::set('database.default', 'tenant');
    }
}
