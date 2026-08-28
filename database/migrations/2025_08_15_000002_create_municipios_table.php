<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('municipios', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('codigo', 10)->unique();
            $table->foreignId('provincia_id')->constrained('provincias')->cascadeOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['nome', 'provincia_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipios');
    }
};
