<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('croqui_id')
                  ->constrained('croquis')
                  ->cascadeOnDelete();

            $table->unsignedBigInteger('municipio_id')->nullable();
            $table->foreign('municipio_id')->references('id')->on('municipios')->nullOnDelete();

            // Dados RUPE
            $table->decimal('valor', 12, 2)->nullable();
            $table->string('rupe_referencia', 50)->nullable();
            $table->string('rupe_entidade', 20)->nullable();
            $table->date('rupe_data_limite')->nullable();

            // Comprovativo de pagamento
            $table->string('comprovativo_path')->nullable();
            $table->string('comprovativo_nome')->nullable();
            $table->string('comprovativo_mime', 50)->nullable();

            // Estado do fluxo
            $table->string('estado', 40)->default('aguardando_pagamento');

            // Textos livres
            $table->text('observacoes')->nullable();
            $table->text('motivo_rejeicao')->nullable();

            // Rastreabilidade
            $table->foreignId('criado_por')->nullable()->constrained('users')->nullOnDelete();

            $table->unsignedBigInteger('rupe_gerado_por')->nullable();
            $table->foreign('rupe_gerado_por')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('rupe_gerado_em')->nullable();

            $table->unsignedBigInteger('comprovativo_por')->nullable();
            $table->foreign('comprovativo_por')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('comprovativo_em')->nullable();

            $table->unsignedBigInteger('confirmado_por')->nullable();
            $table->foreign('confirmado_por')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('confirmado_em')->nullable();

            $table->timestamps();

            // Garantir um pagamento activo por croqui (pode haver histórico mas um único registo corrente)
            $table->index(['croqui_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
