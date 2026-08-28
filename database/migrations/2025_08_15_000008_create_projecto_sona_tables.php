<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Projecto SONA
        Schema::create('projecto_sona', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('tipo'); // requalificacao, avancos_obras
            $table->string('fase')->nullable();
            $table->string('estado')->default('em_curso'); // em_curso, concluido, suspenso
            $table->foreignId('criado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // Ficheiros do projecto SONA (imagens, PDFs, etc.)
        Schema::create('projecto_sona_ficheiros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projecto_sona_id')->constrained('projecto_sona')->cascadeOnDelete();
            $table->string('nome_original');
            $table->string('caminho');
            $table->string('tipo_ficheiro');
            $table->string('mime_type');
            $table->unsignedBigInteger('tamanho');
            $table->string('fase')->nullable();
            $table->text('legenda')->nullable();
            $table->foreignId('enviado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projecto_sona_ficheiros');
        Schema::dropIfExists('projecto_sona');
    }
};
