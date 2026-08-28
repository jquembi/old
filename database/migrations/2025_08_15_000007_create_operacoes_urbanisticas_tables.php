<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Operações urbanísticas - arquivo base QGIS gerido pelo admin
        Schema::create('operacoes_urbanisticas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('ficheiro_base_caminho')->nullable(); // arquivo QGIS base
            $table->string('ficheiro_base_nome')->nullable();
            $table->foreignId('criado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // Croquis elaborados (1.4.1 e 1.4.2)
        Schema::create('croquis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operacao_id')->constrained('operacoes_urbanisticas')->cascadeOnDelete();
            $table->string('tipo'); // elaborado, terreno_ocioso
            $table->string('ficheiro_pdf_caminho')->nullable();
            $table->string('ficheiro_pdf_nome')->nullable();
            $table->string('condicao')->nullable(); // legal, ilegal, em_conflito (para tipo=elaborado)
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('coordenadas_texto')->nullable();
            $table->text('observacoes')->nullable();
            $table->foreignId('criado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // Proprietários dos croquis
        Schema::create('croqui_proprietarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('croqui_id')->constrained('croquis')->cascadeOnDelete();
            $table->string('nome');
            $table->string('bi')->nullable(); // bilhete de identidade
            $table->string('contacto')->nullable();
            $table->timestamps();
        });

        // Pareceres do INOTU (1.4.3)
        Schema::create('pareceres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('projecto_nome');
            $table->string('estado')->default('pendente'); // pendente, aprovado, rejeitado
            $table->string('ficheiro_caminho')->nullable();
            $table->string('ficheiro_nome')->nullable();
            $table->foreignId('criado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pareceres');
        Schema::dropIfExists('croqui_proprietarios');
        Schema::dropIfExists('croquis');
        Schema::dropIfExists('operacoes_urbanisticas');
    }
};
