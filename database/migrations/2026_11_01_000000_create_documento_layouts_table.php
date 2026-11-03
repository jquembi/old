<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documento_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_documento')->unique();
            $table->string('nome')->nullable();
            $table->string('formato_papel')->default('A4');
            $table->string('orientacao')->default('retrato');
            $table->json('blocos')->nullable();
            $table->json('institucional')->nullable();
            $table->boolean('ativo')->default(true);
            $table->unsignedBigInteger('atualizado_por')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_layouts');
    }
};
