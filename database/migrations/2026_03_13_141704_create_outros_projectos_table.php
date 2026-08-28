<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('outros_projectos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('tipo')->default('requalificacao'); // requalificacao, infraestrutura, social, outro
            $table->string('entidade_responsavel')->nullable();
            $table->string('fase')->nullable();
            $table->string('estado')->default('em_curso'); // em_curso, concluido, suspenso, planificado
            $table->date('data_inicio')->nullable();
            $table->date('data_previsao_fim')->nullable();
            $table->decimal('orcamento', 18, 2)->nullable();
            $table->text('observacoes')->nullable();
            $table->foreignId('criado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outros_projectos');
    }
};
