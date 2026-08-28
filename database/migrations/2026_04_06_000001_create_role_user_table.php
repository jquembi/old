<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Criar tabela pivot role_user
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->primary(['user_id', 'role_id']);
        });

        // 2. Migrar dados existentes: copiar role_id para a pivot
        $users = DB::table('users')->whereNotNull('role_id')->get(['id', 'role_id']);
        foreach ($users as $user) {
            DB::table('role_user')->insert([
                'user_id' => $user->id,
                'role_id' => $user->role_id,
            ]);
        }

        // 3. Remover coluna role_id da tabela users
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }

    public function down(): void
    {
        // 1. Restaurar coluna role_id na tabela users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles')->nullOnDelete();
        });

        // 2. Migrar dados de volta: pegar a primeira role de cada user
        $pivots = DB::table('role_user')
            ->select('user_id', DB::raw('MIN(role_id) as role_id'))
            ->groupBy('user_id')
            ->get();

        foreach ($pivots as $pivot) {
            DB::table('users')->where('id', $pivot->user_id)->update(['role_id' => $pivot->role_id]);
        }

        // 3. Remover tabela pivot
        Schema::dropIfExists('role_user');
    }
};
