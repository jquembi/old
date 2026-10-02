<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = config('rule-manager.table_names');

        if (! Schema::hasTable($tables['roles']) || ! Schema::hasTable($tables['permissions'])) {
            throw new RuntimeException('As tabelas do Rule Manager devem existir antes da migração do ACL legado.');
        }

        if (! Schema::hasColumn($tables['roles'], 'grupo')) {
            Schema::table($tables['roles'], fn ($table) => $table->string('grupo')->nullable()->after('descricao'));
        }

        if (! Schema::hasTable('roles') || ! Schema::hasTable('permissoes')) {
            return;
        }

        $legacyPermissions = DB::table('permissoes')->get();
        foreach ($legacyPermissions as $permission) {
            DB::table($tables['permissions'])->updateOrInsert(
                ['slug' => $permission->nome, 'guard_name' => 'web'],
                [
                    'nome' => $permission->nome,
                    'modulo' => $permission->grupo,
                    'descricao' => $permission->descricao,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }

        $legacyRoles = DB::table('roles')->get();
        foreach ($legacyRoles as $role) {
            DB::table($tables['roles'])->updateOrInsert(
                ['slug' => $role->nome, 'guard_name' => 'web', 'empresa_id' => null],
                [
                    'nome' => $role->nome,
                    'descricao' => $role->descricao,
                    'grupo' => $role->grupo ?? null,
                    'nivel_hierarquico' => $role->nivel,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }

        $roleIds = DB::table($tables['roles'])->pluck('id', 'slug');
        $permissionIds = DB::table($tables['permissions'])->pluck('id', 'slug');

        if (Schema::hasTable('role_permissao')) {
            foreach (DB::table('role_permissao')->get() as $assignment) {
                $legacyRole = $legacyRoles->firstWhere('id', $assignment->role_id);
                $legacyPermission = $legacyPermissions->firstWhere('id', $assignment->permissao_id);

                if (! $legacyRole || ! $legacyPermission) {
                    continue;
                }

                $roleId = $roleIds[$legacyRole->nome] ?? null;
                $permissionId = $permissionIds[$legacyPermission->nome] ?? null;

                if ($roleId && $permissionId) {
                    DB::table($tables['role_has_permissions'])->updateOrInsert([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                    ]);
                }
            }
        }

        // No ACL antigo estes perfis tinham acesso implícito. A nova matriz
        // torna-o explícito e auditável, sem conceder permissões a outros papéis.
        $allPermissionIds = $permissionIds->values();
        foreach (['director_inotu', 'super_admin_nacional'] as $administratorSlug) {
            $roleId = $roleIds[$administratorSlug] ?? null;

            if (! $roleId) {
                continue;
            }

            foreach ($allPermissionIds as $permissionId) {
                DB::table($tables['role_has_permissions'])->updateOrInsert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }

        if (! Schema::hasTable('role_user')) {
            return;
        }

        foreach (DB::table('role_user')->get() as $assignment) {
            $legacyRole = $legacyRoles->firstWhere('id', $assignment->role_id);
            $roleId = $legacyRole ? ($roleIds[$legacyRole->nome] ?? null) : null;

            if ($roleId) {
                DB::table($tables['model_has_roles'])->updateOrInsert([
                    'role_id' => $roleId,
                    'model_id' => $assignment->user_id,
                    'model_type' => App\Models\User::class,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Os dados legados são preservados e os dados migrados permanecem para
        // garantir rollback operacional sem remoção automática de privilégios.
    }
};
