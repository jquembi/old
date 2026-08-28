<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Roles do sistema
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique(); // super_admin, admin_provincial, admin_municipal, tecnico_municipal
            $table->string('descricao');
            $table->integer('nivel'); // 1=INOTU, 2=Gov.Provincial, 3=Admin.Municipal, 4=Tecnico.Municipal
            $table->timestamps();
        });

        // Permissões do sistema
        Schema::create('permissoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique(); // ex: instrumentos.ver, instrumentos.criar, usuarios.gerir
            $table->string('descricao');
            $table->string('grupo'); // agrupamento lógico
            $table->timestamps();
        });

        // Pivot: role <-> permissão
        Schema::create('role_permissao', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permissao_id')->constrained('permissoes')->cascadeOnDelete();
            $table->primary(['role_id', 'permissao_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissao');
        Schema::dropIfExists('permissoes');
        Schema::dropIfExists('roles');
    }
};
