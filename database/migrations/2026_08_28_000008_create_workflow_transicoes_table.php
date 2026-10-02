<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_transicoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_workflow_id')->constrained('processo_workflows')->onDelete('cascade');
            $table->string('de_estado');
            $table->string('para_estado');
            $table->foreignId('realizado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->json('dados')->nullable(); // Dados da transição
            $table->text('observacoes')->nullable();
            $table->timestamps();
            
            $table->index('processo_workflow_id');
            $table->index(['de_estado', 'para_estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_transicoes');
    }
};
