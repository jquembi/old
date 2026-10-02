<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processo_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('restrict');
            $table->morphs('processo'); // processo_id, processo_type (Operacao, TerrenoOcioso, etc.)
            $table->string('estado_actual');
            $table->json('dados')->nullable(); // Dados específicos do processo
            $table->foreignId('iniciado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('iniciado_em')->nullable();
            $table->timestamp('concluido_em')->nullable();
            $table->timestamps();
            
            $table->index(['processo_id', 'processo_type']);
            $table->index('estado_actual');
            $table->index('iniciado_em');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processo_workflows');
    }
};
