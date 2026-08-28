<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Categorias de instrumentos (ordenamento, urbanísticos, cartas, etc.)
        Schema::create('categoria_instrumentos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->text('descricao')->nullable();
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        // Tipos de instrumentos dentro de cada categoria
        Schema::create('tipo_instrumentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categoria_instrumentos')->cascadeOnDelete();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->text('descricao')->nullable();
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        // Instrumentos enviados por município
        Schema::create('instrumentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnDelete();
            $table->foreignId('tipo_instrumento_id')->constrained('tipo_instrumentos')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('estado')->default('rascunho'); // rascunho, submetido, aprovado, rejeitado
            $table->string('ano')->nullable();
            $table->foreignId('criado_por')->constrained('users')->cascadeOnDelete();
            $table->foreignId('actualizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Ficheiros anexados aos instrumentos
        Schema::create('instrumento_ficheiros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrumento_id')->constrained('instrumentos')->cascadeOnDelete();
            $table->string('nome_original');
            $table->string('caminho');
            $table->string('tipo_ficheiro'); // pdf, qgis, dwg, arcgis, kmz, etc.
            $table->string('mime_type');
            $table->unsignedBigInteger('tamanho'); // bytes
            $table->foreignId('enviado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrumento_ficheiros');
        Schema::dropIfExists('instrumentos');
        Schema::dropIfExists('tipo_instrumentos');
        Schema::dropIfExists('categoria_instrumentos');
    }
};
