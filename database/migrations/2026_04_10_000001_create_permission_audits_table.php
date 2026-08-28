<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->string('permissao'); // Ex: 'instrumentos.editar'
            $table->boolean('permitido'); // true/false
            $table->json('contexto')->nullable(); // Contexto da permissão
            $table->text('motivo')->nullable(); // Motivo de negação
            $table->ipAddress('ip')->nullable(); // IP do utilizador
            $table->text('user_agent')->nullable(); // User Agent
            $table->timestamp('criado_em')->useCurrent();

            $table->index(['usuario_id', 'criado_em']);
            $table->index(['permissao', 'criado_em']);
            $table->index(['permitido', 'criado_em']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_audits');
    }
};
