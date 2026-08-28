<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('croquis') ||
            ! Schema::hasTable('roles') ||
            ! Schema::hasTable('role_user') ||
            ! Schema::hasColumn('croquis', 'criado_por')
        ) {
            return;
        }

        DB::table('croquis')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('role_user')
                    ->join('roles', 'roles.id', '=', 'role_user.role_id')
                    ->whereColumn('role_user.user_id', 'croquis.criado_por')
                    ->where('roles.nivel', Role::NIVEL_INOTU);
            })
            ->update([
                'rup_pago' => true,
                'rup_referencia' => 'DISPENSADO-INOTU',
                'rup_pago_em' => now(),
                'download_liberado' => true,
                'liberado_por' => DB::raw('criado_por'),
                'liberado_em' => now(),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Nao revertemos homologacoes automaticas para evitar bloquear documentos
        // que ja tenham sido disponibilizados depois desta regra de negocio.
    }
};
