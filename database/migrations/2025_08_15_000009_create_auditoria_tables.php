<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Logs de acesso
        Schema::create('logs_acesso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('accao'); // login, logout, ver, criar, editar, eliminar, upload, download
            $table->string('recurso')->nullable(); // nome do modelo/recurso
            $table->unsignedBigInteger('recurso_id')->nullable();
            $table->text('detalhes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        // Auditoria de alterações
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('accao'); // criar, editar, eliminar
            $table->string('modelo'); // nome do modelo
            $table->unsignedBigInteger('modelo_id');
            $table->json('valores_antigos')->nullable();
            $table->json('valores_novos')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
        Schema::dropIfExists('logs_acesso');
    }
};
