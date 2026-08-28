<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arquivos_base', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnDelete();
            $table->string('tipo'); // terrenos_ociosos, pareceres
            $table->string('ficheiro_caminho');
            $table->string('ficheiro_nome');
            $table->foreignId('enviado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['municipio_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arquivos_base');
    }
};
