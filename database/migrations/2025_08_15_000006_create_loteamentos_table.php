<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Projectos de loteamento
        Schema::create('loteamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnDelete();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('estado'); // aprovado, concluido, em_curso
            $table->string('localizacao')->nullable();
            $table->foreignId('criado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // Ficheiros de loteamento
        Schema::create('loteamento_ficheiros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loteamento_id')->constrained('loteamentos')->cascadeOnDelete();
            $table->string('nome_original');
            $table->string('caminho');
            $table->string('tipo_ficheiro');
            $table->string('mime_type');
            $table->unsignedBigInteger('tamanho');
            $table->foreignId('enviado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loteamento_ficheiros');
        Schema::dropIfExists('loteamentos');
    }
};
