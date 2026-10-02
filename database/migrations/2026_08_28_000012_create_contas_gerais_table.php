<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contas_gerais', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->enum('tipo', ['ativo', 'passivo', 'receita', 'despesa']);
            $table->enum('nivel', [1, 2, 3, 4, 5])->default(1);
            $table->foreignId('conta_pai_id')->nullable()->constrained('contas_gerais')->onDelete('set null');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index('codigo');
            $table->index('tipo');
            $table->index('nivel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contas_gerais');
    }
};
