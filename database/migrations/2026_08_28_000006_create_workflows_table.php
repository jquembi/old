<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tipo_processo', 100); // processo_fundiario, licenciamento, etc.
            $table->text('description')->nullable();
            $table->json('configuracao'); // Definição de estados e transições
            $table->boolean('activo')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('tipo_processo');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
