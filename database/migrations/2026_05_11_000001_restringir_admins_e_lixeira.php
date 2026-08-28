<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ADMIN_INFO_MODULES = [
        'instrumentos',
        'loteamentos',
        'operacoes',
        'croquis',
        'pareceres',
        'sona',
        'cartas_municipais',
        'terrenos_ociosos',
        'requalificacao',
        'outros_projectos',
        'croquis_elaborados',
        'ambiente_trabalho',
        'sig_editor',
        'documentos',
    ];

    private const ADMIN_INFO_PERMISSOES = [
        'dashboard.ver',
        'dashboard.municipal',
        'dashboard.tecnico',
        'analises.acessar',
        'estatisticas.ver',
        'relatorios.gerar',
        'relatorios.exportar',
        'municipios.ver_dados',
        'sistema.ver_logs',
        'sistema.monitorizar',
        'documentos.ver',
        'documentos.download',
    ];

    public function up(): void
    {
        $this->restringirAdmins();
        $this->garantirDirectorPrincipal();
    }

    public function down(): void
    {
        // Nao reatribui permissoes removidas para evitar reabrir acesso administrativo perigoso.
    }

    private function restringirAdmins(): void
    {
        $roleIds = DB::table('roles')
            ->whereIn('nome', [
                Role::CHEFE_SECCAO_ADMINISTRATIVA,
                Role::ADMIN_MUNICIPAL,
                Role::ADMIN_MUNICIPAL_ADJUNTO,
            ])
            ->pluck('id')
            ->all();

        if ($roleIds === []) {
            return;
        }

        $permissaoIds = DB::table('permissoes')
            ->get(['id', 'nome'])
            ->filter(fn ($permissao) => self::isPermissaoInformacaoTecnica($permissao->nome))
            ->pluck('id')
            ->all();

        DB::table('role_permissao')->whereIn('role_id', $roleIds)->delete();

        foreach ($roleIds as $roleId) {
            $linhas = array_map(fn ($permissaoId) => [
                'role_id' => $roleId,
                'permissao_id' => $permissaoId,
            ], $permissaoIds);

            if ($linhas !== []) {
                DB::table('role_permissao')->insertOrIgnore($linhas);
            }
        }
    }

    private function garantirDirectorPrincipal(): void
    {
        $directorId = DB::table('roles')
            ->where('nome', Role::DIRECTOR_INOTU)
            ->value('id');

        if (! $directorId) {
            return;
        }

        $linhas = DB::table('permissoes')
            ->pluck('id')
            ->map(fn ($permissaoId) => [
                'role_id' => $directorId,
                'permissao_id' => $permissaoId,
            ])
            ->all();

        if ($linhas !== []) {
            DB::table('role_permissao')->insertOrIgnore($linhas);
        }
    }

    private static function isPermissaoInformacaoTecnica(string $permissao): bool
    {
        if (in_array($permissao, self::ADMIN_INFO_PERMISSOES, true)) {
            return true;
        }

        [$modulo, $accao] = array_pad(explode('.', $permissao, 2), 2, '');

        if (! in_array($modulo, self::ADMIN_INFO_MODULES, true)) {
            return false;
        }

        return $accao === 'ver'
            || str_starts_with($accao, 'ver_')
            || in_array($accao, ['acessar', 'download', 'exportar'], true);
    }
};
