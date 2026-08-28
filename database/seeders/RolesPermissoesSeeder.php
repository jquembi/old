<?php

namespace Database\Seeders;

use App\Models\Permissao;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesPermissoesSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Roles INOTU (nível 1) ──────────────────────────
        $chefeSeccao = Role::firstOrCreate(
            ['nome' => Role::CHEFE_SECCAO_TECNICA],
            ['descricao' => 'Chefe de Secção para Área Técnica', 'grupo' => Role::GRUPO_INOTU, 'nivel' => Role::NIVEL_INOTU]
        );

        $secretariaGabinete = Role::firstOrCreate(
            ['nome' => Role::SECRETARIA_GABINETE],
            ['descricao' => 'Secretária do Gabinete do Director', 'grupo' => Role::GRUPO_INOTU, 'nivel' => Role::NIVEL_INOTU]
        );

        $directorInotu = Role::firstOrCreate(
            ['nome' => Role::DIRECTOR_INOTU],
            ['descricao' => 'Director do INOTU', 'grupo' => Role::GRUPO_INOTU, 'nivel' => Role::NIVEL_INOTU]
        );

        $tecnicoInotu = Role::firstOrCreate(
            ['nome' => Role::TECNICO_INOTU],
            ['descricao' => 'Técnico do INOTU', 'grupo' => Role::GRUPO_INOTU, 'nivel' => Role::NIVEL_INOTU]
        );

        // ─── Roles Governo Provincial (nível 2) ─────────────
        $governador = Role::firstOrCreate(
            ['nome' => Role::GOVERNADOR],
            ['descricao' => 'Governador', 'grupo' => Role::GRUPO_GOVERNO, 'nivel' => Role::NIVEL_PROVINCIAL]
        );

        $viceGovernador = Role::firstOrCreate(
            ['nome' => Role::VICE_GOVERNADOR_TECNICO],
            ['descricao' => 'Vice-Governador para Área Técnica', 'grupo' => Role::GRUPO_GOVERNO, 'nivel' => Role::NIVEL_PROVINCIAL]
        );

        $assessor = Role::firstOrCreate(
            ['nome' => Role::ASSESSOR_VICE_GOVERNADOR],
            ['descricao' => 'Assessor do Vice-Governador para Área Técnica', 'grupo' => Role::GRUPO_GOVERNO, 'nivel' => Role::NIVEL_PROVINCIAL]
        );

        // ─── Roles Administração Municipal (nível 3) ────────
        $adminMunicipal = Role::firstOrCreate(
            ['nome' => Role::ADMIN_MUNICIPAL],
            ['descricao' => 'Administrador Municipal', 'grupo' => Role::GRUPO_ADMINISTRACAO, 'nivel' => Role::NIVEL_MUNICIPAL]
        );

        $adminAdjunto = Role::firstOrCreate(
            ['nome' => Role::ADMIN_MUNICIPAL_ADJUNTO],
            ['descricao' => 'Administrador Municipal Adjunto para Área Técnica', 'grupo' => Role::GRUPO_ADMINISTRACAO, 'nivel' => Role::NIVEL_MUNICIPAL]
        );

        $directorInfra = Role::firstOrCreate(
            ['nome' => Role::DIRECTOR_INFRAESTRUTURA],
            ['descricao' => 'Director Municipal de Infraestrutura', 'grupo' => Role::GRUPO_ADMINISTRACAO, 'nivel' => Role::NIVEL_MUNICIPAL]
        );

        // ─── Técnicos (nível 4) ─────────────────────────────
        $tecnicoOrdenamento = Role::firstOrCreate(
            ['nome' => Role::TECNICO_ORDENAMENTO],
            ['descricao' => 'Técnicos do Ordenamento do Território', 'grupo' => Role::GRUPO_ADMINISTRACAO, 'nivel' => Role::NIVEL_TECNICO]
        );

        // ─── Permissões ─────────────────────────────────────
        $permissoes = [
            // Instrumentos
            ['nome' => 'instrumentos.ver', 'descricao' => 'Ver instrumentos', 'grupo' => 'Instrumentos'],
            ['nome' => 'instrumentos.criar', 'descricao' => 'Criar instrumentos', 'grupo' => 'Instrumentos'],
            ['nome' => 'instrumentos.editar', 'descricao' => 'Editar instrumentos', 'grupo' => 'Instrumentos'],
            ['nome' => 'instrumentos.eliminar', 'descricao' => 'Eliminar instrumentos', 'grupo' => 'Instrumentos'],
            ['nome' => 'instrumentos.upload', 'descricao' => 'Upload de ficheiros nos instrumentos', 'grupo' => 'Instrumentos'],

            // Loteamentos
            ['nome' => 'loteamentos.ver', 'descricao' => 'Ver loteamentos', 'grupo' => 'Loteamentos'],
            ['nome' => 'loteamentos.criar', 'descricao' => 'Criar loteamentos', 'grupo' => 'Loteamentos'],
            ['nome' => 'loteamentos.editar', 'descricao' => 'Editar loteamentos', 'grupo' => 'Loteamentos'],
            ['nome' => 'loteamentos.eliminar', 'descricao' => 'Eliminar loteamentos', 'grupo' => 'Loteamentos'],

            // Operações Urbanísticas
            ['nome' => 'operacoes.ver', 'descricao' => 'Ver operações urbanísticas', 'grupo' => 'Operações'],
            ['nome' => 'operacoes.criar', 'descricao' => 'Criar operações urbanísticas', 'grupo' => 'Operações'],
            ['nome' => 'operacoes.editar', 'descricao' => 'Editar operações urbanísticas', 'grupo' => 'Operações'],
            ['nome' => 'operacoes.eliminar', 'descricao' => 'Eliminar operações urbanísticas', 'grupo' => 'Operações'],

            // Croquis
            ['nome' => 'croquis.ver', 'descricao' => 'Ver croquis', 'grupo' => 'Croquis'],
            ['nome' => 'croquis.criar', 'descricao' => 'Criar croquis', 'grupo' => 'Croquis'],
            ['nome' => 'croquis.editar', 'descricao' => 'Editar croquis', 'grupo' => 'Croquis'],
            ['nome' => 'croquis.eliminar', 'descricao' => 'Eliminar croquis', 'grupo' => 'Croquis'],

            // Pareceres
            ['nome' => 'pareceres.ver', 'descricao' => 'Ver pareceres', 'grupo' => 'Pareceres'],
            ['nome' => 'pareceres.criar', 'descricao' => 'Criar pareceres', 'grupo' => 'Pareceres'],
            ['nome' => 'pareceres.editar', 'descricao' => 'Editar pareceres', 'grupo' => 'Pareceres'],
            ['nome' => 'pareceres.eliminar', 'descricao' => 'Eliminar pareceres', 'grupo' => 'Pareceres'],

            // Projecto SONA
            ['nome' => 'sona.ver', 'descricao' => 'Ver projecto SONA', 'grupo' => 'SONA'],
            ['nome' => 'sona.criar', 'descricao' => 'Criar projecto SONA', 'grupo' => 'SONA'],
            ['nome' => 'sona.editar', 'descricao' => 'Editar projecto SONA', 'grupo' => 'SONA'],
            ['nome' => 'sona.eliminar', 'descricao' => 'Eliminar projecto SONA', 'grupo' => 'SONA'],

            // Gestão de Utilizadores
            ['nome' => 'usuarios.ver', 'descricao' => 'Ver utilizadores', 'grupo' => 'Utilizadores'],
            ['nome' => 'usuarios.criar', 'descricao' => 'Criar utilizadores', 'grupo' => 'Utilizadores'],
            ['nome' => 'usuarios.editar', 'descricao' => 'Editar utilizadores', 'grupo' => 'Utilizadores'],
            ['nome' => 'usuarios.desactivar', 'descricao' => 'Desactivar utilizadores', 'grupo' => 'Utilizadores'],

            // Auditoria
            ['nome' => 'auditoria.ver', 'descricao' => 'Ver logs de auditoria', 'grupo' => 'Auditoria'],
            ['nome' => 'logs.ver', 'descricao' => 'Ver logs de acesso', 'grupo' => 'Auditoria'],

            // Dashboard
            ['nome' => 'dashboard.ver', 'descricao' => 'Ver dashboard geral', 'grupo' => 'Dashboard'],
            ['nome' => 'dashboard.provincial', 'descricao' => 'Ver dashboard provincial', 'grupo' => 'Dashboard'],
            ['nome' => 'dashboard.municipal', 'descricao' => 'Ver dashboard municipal', 'grupo' => 'Dashboard'],
        ];

        $permissoesCriadas = [];
        foreach ($permissoes as $p) {
            $permissoesCriadas[$p['nome']] = Permissao::firstOrCreate(
                ['nome' => $p['nome']],
                ['descricao' => $p['descricao'], 'grupo' => $p['grupo']]
            );
        }

        // ─── Atribuir permissões às roles ────────────────────

        // INOTU — acesso total a todos os módulos
        $allPermIds = Permissao::pluck('id')->all();
        $chefeSeccao->permissoes()->syncWithoutDetaching($allPermIds);
        $secretariaGabinete->permissoes()->syncWithoutDetaching($allPermIds);
        $directorInotu->permissoes()->syncWithoutDetaching($allPermIds);
        $tecnicoInotu->permissoes()->syncWithoutDetaching($allPermIds);

        // Governo Provincial — visualização + gestão de utilizadores + auditoria
        $provincialPermissoes = [
            'instrumentos.ver', 'loteamentos.ver', 'operacoes.ver',
            'croquis.ver', 'pareceres.ver', 'sona.ver',
            'usuarios.ver', 'usuarios.criar', 'usuarios.editar', 'usuarios.desactivar',
            'logs.ver', 'auditoria.ver',
            'dashboard.ver', 'dashboard.provincial',
        ];
        $provincialPermIds = collect($provincialPermissoes)->map(fn ($n) => $permissoesCriadas[$n]->id)->toArray();
        $governador->permissoes()->syncWithoutDetaching($provincialPermIds);
        $viceGovernador->permissoes()->syncWithoutDetaching($provincialPermIds);
        $assessor->permissoes()->syncWithoutDetaching($provincialPermIds);

        // Administração Municipal — ver + criar + editar dados do município
        $municipalPermissoes = [
            'instrumentos.ver',
            'loteamentos.ver',
            'operacoes.ver',
            'croquis.ver',
            'pareceres.ver',
            'sona.ver',
            'dashboard.municipal',
        ];
        $municipalPermIds = collect($municipalPermissoes)->map(fn ($n) => $permissoesCriadas[$n]->id)->toArray();
        $adminMunicipal->permissoes()->sync($municipalPermIds);
        $adminAdjunto->permissoes()->sync($municipalPermIds);
        $directorInfra->permissoes()->syncWithoutDetaching($municipalPermIds);

        // Técnicos — trabalho operacional
        $tecnicoPermissoes = [
            'instrumentos.ver', 'instrumentos.criar', 'instrumentos.upload',
            'loteamentos.ver', 'loteamentos.criar',
            'operacoes.ver',
            'croquis.ver', 'croquis.criar', 'croquis.editar',
            'pareceres.ver',
            'sona.ver', 'sona.criar',
            'dashboard.municipal',
        ];
        $tecnicoOrdenamento->permissoes()->syncWithoutDetaching(
            collect($tecnicoPermissoes)->map(fn ($n) => $permissoesCriadas[$n]->id)->toArray()
        );
    }
}
