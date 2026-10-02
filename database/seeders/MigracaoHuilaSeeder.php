<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use App\Models\Municipio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MigracaoHuilaSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== INICIANDO MIGRAÇÃO DO SISTEMA HUÍLA ===');
        
        // Obter ou criar tenant Huíla
        $tenant = $this->criarTenantHuila();
        
        if (!$tenant) {
            $this->command->error('Falha ao criar tenant Huíla!');
            return;
        }
        
        $this->command->info("Tenant Huíla criado: {$tenant->name} (ID: {$tenant->id})");
        
        // Executar migrações em ordem
        $this->migrarMunicipios($tenant);
        $this->migrarUsuarios($tenant);
        $this->migrarRolesPermissoes($tenant);
        $this->migrarDadosPrincipais($tenant);
        $this->migrarDocumentos($tenant);
        $this->migrarAuditoria($tenant);
        
        $this->command->info('=== MIGRAÇÃO CONCLUÍDA COM SUCESSO ===');
    }
    
    private function criarTenantHuila(): ?Tenant
    {
        $this->command->info('Criando tenant Huíla...');
        
        // Verificar se já existe
        $tenant = Tenant::where('code', 'HUI')->first();
        
        if ($tenant) {
            $this->command->info('Tenant Huíla já existe, utilizando existente.');
            return $tenant;
        }
        
        // Criar novo tenant
        return Tenant::create([
            'code' => 'HUI',
            'name' => 'Província de Huíla',
            'type' => 'provincial',
            'is_active' => true,
            'is_online' => true,
            'database_host' => env('DB_HOST', 'localhost'),
            'database_port' => env('DB_PORT', '5432'),
            'database_name' => env('DB_DATABASE', 'gterra_huila'),
            'database_username' => env('DB_USERNAME', 'postgres'),
            'database_password' => env('DB_PASSWORD', ''),
            'sync_status' => 'pending',
        ]);
    }
    
    private function migrarMunicipios(Tenant $tenant): void
    {
        $this->command->info('Validando municípios da Huíla...');
        
        // Municípios já existem na tabela central
        // Apenas verificar se os municípios da Huíla estão presentes
        $municipiosHuila = [
            'Lubango', 'Humpata', 'Chibia', 'Quipungo', 'Cacula',
            'Cuvango', 'Matala', 'Chipindo', 'Jamba', 'Caconda',
            'Caluquembe', 'Chicomba', 'Gambos', 'Chicuma', 'Matala',
            'Quilengues', 'Cuchi', 'Kuvango'
        ];
        
        $encontrados = 0;
        foreach ($municipiosHuila as $nome) {
            $municipio = Municipio::where('nome', 'like', "%{$nome}%")->first();
            if ($municipio) {
                $encontrados++;
            }
        }
        
        $this->command->info("Municípios encontrados: {$encontrados}/" . count($municipiosHuila));
    }
    
    private function migrarUsuarios(Tenant $tenant): void
    {
        $this->command->info('Validando usuários da Huíla...');
        
        // Usuários já têm provincia_id associado
        // Apenas verificar contagem
        $usuariosHuila = User::whereHas('provincia', function ($query) {
            $query->where('codigo', 'HUI');
        })->count();
        
        $this->command->info("Usuários da Huíla: {$usuariosHuila}");
    }
    
    private function migrarRolesPermissoes(Tenant $tenant): void
    {
        $this->command->info('Validando roles e permissões...');
        
        // Roles e permissões são globais
        // Verificar se roles principais existem
        $roles = Role::whereIn('nome', [
            Role::ADMINISTRADOR,
            Role::GOVERNADOR,
            Role::ADMIN_MUNICIPAL,
            Role::TECNICO,
        ])->count();
        
        $this->command->info("Roles principais encontrados: {$roles}");
    }
    
    private function migrarDadosPrincipais(Tenant $tenant): void
    {
        $this->command->info('Adicionando sync fields aos dados principais...');
        
        $tabelas = [
            'operacoes' => 'Operações Urbanísticas',
            'instrumentos' => 'Instrumentos de Planeamento',
            'loteamentos' => 'Loteamentos',
            'croquis' => 'Croquis',
            'pareceres' => 'Pareceres Técnicos',
            'projectos_sona' => 'Projetos SONA',
            'outros_projectos' => 'Outros Projetos',
            'terrenos_ociosos' => 'Terrenos Ociosos',
        ];
        
        $totalRegistros = 0;
        
        foreach ($tabelas as $tabela => $nome) {
            $this->command->info("Processando {$nome}...");
            
            // Verificar se tabela existe
            if (!DB::getSchemaBuilder()->hasTable($tabela)) {
                $this->command->warn("Tabela {$tabela} não existe, pulando.");
                continue;
            }
            
            // Adicionar sync_id aos registros que ainda não têm
            $semSync = DB::table($tabela)->whereNull('sync_id')->count();
            
            if ($semSync > 0) {
                DB::table($tabela)
                    ->whereNull('sync_id')
                    ->update([
                        'sync_id' => $this->generateUUID(),
                        'sync_status' => 'pending',
                        'sync_version' => 1,
                        'synced_at' => null,
                        'sync_error' => null,
                    ]);
                
                $this->command->info("  - {$semSync} registros atualizados com sync_id");
            } else {
                $this->command->info("  - Todos os registros já têm sync_id");
            }
            
            $total = DB::table($tabela)->count();
            $totalRegistros += $total;
            $this->command->info("  - Total: {$total} registros");
        }
        
        $this->command->info("Total de registros processados: {$totalRegistros}");
    }
    
    private function migrarDocumentos(Tenant $tenant): void
    {
        $this->command->info('Adicionando campos DMS aos documentos...');
        
        if (!DB::getSchemaBuilder()->hasTable('arquivos_base')) {
            $this->command->warn('Tabela arquivos_base não existe, pulando.');
            return;
        }
        
        // Adicionar campos DMS aos arquivos que ainda não têm
        $semVersao = DB::table('arquivos_base')->whereNull('versao')->count();
        
        if ($semVersao > 0) {
            DB::table('arquivos_base')
                ->whereNull('versao')
                ->update([
                    'versao' => 1,
                    'versao_anterior_id' => null,
                    'estado_aprovacao' => 'aprovado',
                    'assinatura_digital' => null,
                    'assinado_por' => null,
                    'assinado_em' => null,
                ]);
            
            $this->command->info("  - {$semVersao} documentos atualizados com campos DMS");
        } else {
            $this->command->info("  - Todos os documentos já têm campos DMS");
        }
        
        $total = DB::table('arquivos_base')->count();
        $this->command->info("  - Total de documentos: {$total}");
    }
    
    private function migrarAuditoria(Tenant $tenant): void
    {
        $this->command->info('Migrando logs de auditoria...');
        
        // Verificar se tabela de auditoria antiga existe
        if (!DB::getSchemaBuilder()->hasTable('auditoria')) {
            $this->command->warn('Tabela auditoria não existe, pulando.');
            return;
        }
        
        $migrados = 0;
        
        DB::table('auditoria')
            ->orderBy('created_at')
            ->chunk(100, function ($auditorias) use ($tenant, &$migrados) {
                foreach ($auditorias as $auditoria) {
                    // Verificar se já foi migrado
                    $jaMigrado = DB::table('national_audit_logs')
                        ->where('metadata', 'like', '%"legacy_id":' . $auditoria->id . '%')
                        ->exists();
                    
                    if ($jaMigrado) {
                        continue;
                    }
                    
                    DB::table('national_audit_logs')->insert([
                        'tenant_id' => $tenant->id,
                        'user_id' => $auditoria->user_id ?? null,
                        'action' => $auditoria->acao ?? 'legacy_action',
                        'entity_type' => $auditoria->entidade ?? 'unknown',
                        'entity_id' => $auditoria->entidade_id,
                        'description' => $auditoria->descricao ?? 'Migrado do sistema antigo',
                        'metadata' => json_encode([
                            'legacy_id' => $auditoria->id,
                            'ip_address' => $auditoria->ip ?? null,
                            'user_agent' => $auditoria->user_agent ?? null,
                        ]),
                        'created_at' => $auditoria->created_at,
                        'updated_at' => $auditoria->updated_at ?? now(),
                    ]);
                    
                    $migrados++;
                }
            });
        
        $this->command->info("  - Logs de auditoria migrados: {$migrados}");
    }
    
    private function generateUUID(): string
    {
        return (string) Str::uuid();
    }
}
