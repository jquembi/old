<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Inserir permissões de documentos se não existirem
        $permissoes = [
            [
                'nome'      => 'documentos.ver',
                'descricao' => 'Visualizar documentos PDF',
                'grupo'     => 'documentos',
            ],
            [
                'nome'      => 'documentos.download',
                'descricao' => 'Baixar documentos PDF',
                'grupo'     => 'documentos',
            ],
        ];

        foreach ($permissoes as $perm) {
            DB::table('permissoes')->insertOrIgnore(array_merge($perm, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Atribuir automaticamente a todos os roles que já têm operacoes.ver / instrumentos.ver
        $permIdsNovos = DB::table('permissoes')
            ->whereIn('nome', ['documentos.ver', 'documentos.download'])
            ->pluck('id')
            ->toArray();

        // Roles que têm pelo menos uma das permissões base
        $permissaoBase = DB::table('permissoes')
            ->whereIn('nome', ['operacoes.ver', 'instrumentos.ver', 'pareceres.ver', 'loteamentos.ver'])
            ->pluck('id');

        $rolesComAcesso = DB::table('role_permissao')
            ->whereIn('permissao_id', $permissaoBase)
            ->distinct()
            ->pluck('role_id')
            ->toArray();

        foreach ($rolesComAcesso as $roleId) {
            foreach ($permIdsNovos as $permId) {
                DB::table('role_permissao')->insertOrIgnore([
                    'role_id'      => $roleId,
                    'permissao_id' => $permId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissoes')
            ->whereIn('nome', ['documentos.ver', 'documentos.download'])
            ->pluck('id');

        DB::table('role_permissao')->whereIn('permissao_id', $ids)->delete();
        DB::table('permissoes')->whereIn('nome', ['documentos.ver', 'documentos.download'])->delete();
    }
};
