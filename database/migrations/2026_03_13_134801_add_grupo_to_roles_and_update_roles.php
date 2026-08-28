<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Adicionar coluna grupo
        Schema::table('roles', function (Blueprint $table) {
            $table->string('grupo')->default('')->after('descricao');
        });

        // 2. Remover roles antigos
        DB::table('role_permissao')->whereIn(
            'role_id',
            DB::table('roles')->pluck('id')
        )->delete();
        DB::table('roles')->delete();

        // 3. Inserir novas roles — INOTU (nível 1)
        $chefeId = DB::table('roles')->insertGetId([
            'nome' => Role::CHEFE_SECCAO_TECNICA,
            'descricao' => 'Chefe de Secção para Área Técnica',
            'grupo' => Role::GRUPO_INOTU,
            'nivel' => Role::NIVEL_INOTU,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('roles')->insert([
            'nome' => Role::SECRETARIA_GABINETE,
            'descricao' => 'Secretária do Gabinete do Director',
            'grupo' => Role::GRUPO_INOTU,
            'nivel' => Role::NIVEL_INOTU,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Governo Provincial (nível 2)
        DB::table('roles')->insert([
            [
                'nome' => Role::GOVERNADOR,
                'descricao' => 'Governador',
                'grupo' => Role::GRUPO_GOVERNO,
                'nivel' => Role::NIVEL_PROVINCIAL,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => Role::VICE_GOVERNADOR_TECNICO,
                'descricao' => 'Vice-Governador para Área Técnica',
                'grupo' => Role::GRUPO_GOVERNO,
                'nivel' => Role::NIVEL_PROVINCIAL,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => Role::ASSESSOR_VICE_GOVERNADOR,
                'descricao' => 'Assessor do Vice-Governador para Área Técnica',
                'grupo' => Role::GRUPO_GOVERNO,
                'nivel' => Role::NIVEL_PROVINCIAL,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Administração Municipal (nível 3)
        DB::table('roles')->insert([
            [
                'nome' => Role::ADMIN_MUNICIPAL,
                'descricao' => 'Administrador Municipal',
                'grupo' => Role::GRUPO_ADMINISTRACAO,
                'nivel' => Role::NIVEL_MUNICIPAL,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => Role::ADMIN_MUNICIPAL_ADJUNTO,
                'descricao' => 'Administrador Municipal Adjunto para Área Técnica',
                'grupo' => Role::GRUPO_ADMINISTRACAO,
                'nivel' => Role::NIVEL_MUNICIPAL,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => Role::DIRECTOR_INFRAESTRUTURA,
                'descricao' => 'Director Municipal de Infraestrutura',
                'grupo' => Role::GRUPO_ADMINISTRACAO,
                'nivel' => Role::NIVEL_MUNICIPAL,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Técnicos (nível 4)
        DB::table('roles')->insert([
            'nome' => Role::TECNICO_ORDENAMENTO,
            'descricao' => 'Técnicos do Ordenamento do Território',
            'grupo' => Role::GRUPO_ADMINISTRACAO,
            'nivel' => Role::NIVEL_TECNICO,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Actualizar o admin existente para a nova role
        DB::table('users')->where('role_id', '!=', null)->update(['role_id' => $chefeId]);

        // 5. Re-atribuir permissões
        $allPermIds = DB::table('permissoes')->pluck('id')->toArray();

        // INOTU: todas as permissões aos dois roles
        $inotuRoles = DB::table('roles')->where('nivel', Role::NIVEL_INOTU)->pluck('id');
        foreach ($inotuRoles as $roleId) {
            $pivots = array_map(fn ($pId) => ['role_id' => $roleId, 'permissao_id' => $pId], $allPermIds);
            DB::table('role_permissao')->insert($pivots);
        }

        // Governo Provincial: ver tudo + gerir utilizadores + auditoria
        $provincialPerms = DB::table('permissoes')
            ->where(function ($q) {
                $q->where('nome', 'like', '%.ver')
                  ->orWhereIn('nome', [
                      'usuarios.ver', 'usuarios.criar', 'usuarios.editar', 'usuarios.desactivar',
                      'logs.ver', 'auditoria.ver',
                      'dashboard.ver', 'dashboard.provincial',
                  ]);
            })
            ->pluck('id')->toArray();
        $govRoles = DB::table('roles')->where('nivel', Role::NIVEL_PROVINCIAL)->pluck('id');
        foreach ($govRoles as $roleId) {
            $pivots = array_map(fn ($pId) => ['role_id' => $roleId, 'permissao_id' => $pId], $provincialPerms);
            DB::table('role_permissao')->insert($pivots);
        }

        // Admin Municipal: ver + criar + editar dados
        $municipalPerms = DB::table('permissoes')
            ->whereIn('nome', [
                'instrumentos.ver', 'instrumentos.criar', 'instrumentos.editar', 'instrumentos.upload',
                'loteamentos.ver', 'loteamentos.criar', 'loteamentos.editar',
                'operacoes.ver', 'operacoes.criar', 'operacoes.editar',
                'croquis.ver', 'croquis.criar', 'croquis.editar',
                'pareceres.ver',
                'sona.ver', 'sona.criar', 'sona.editar',
                'usuarios.ver', 'usuarios.criar', 'usuarios.editar',
                'dashboard.municipal',
            ])
            ->pluck('id')->toArray();
        $munRoles = DB::table('roles')->where('nivel', Role::NIVEL_MUNICIPAL)->pluck('id');
        foreach ($munRoles as $roleId) {
            $pivots = array_map(fn ($pId) => ['role_id' => $roleId, 'permissao_id' => $pId], $municipalPerms);
            DB::table('role_permissao')->insert($pivots);
        }

        // Técnicos: trabalho operacional
        $tecnicoPerms = DB::table('permissoes')
            ->whereIn('nome', [
                'instrumentos.ver', 'instrumentos.criar', 'instrumentos.upload',
                'loteamentos.ver', 'loteamentos.criar',
                'operacoes.ver',
                'croquis.ver', 'croquis.criar', 'croquis.editar',
                'pareceres.ver',
                'sona.ver', 'sona.criar',
                'dashboard.municipal',
            ])
            ->pluck('id')->toArray();
        $tecRole = DB::table('roles')->where('nivel', Role::NIVEL_TECNICO)->first();
        if ($tecRole) {
            $pivots = array_map(fn ($pId) => ['role_id' => $tecRole->id, 'permissao_id' => $pId], $tecnicoPerms);
            DB::table('role_permissao')->insert($pivots);
        }
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('grupo');
        });
    }
};
