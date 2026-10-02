<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxas_emolumentos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->enum('tipo', ['taxa', 'emolumento', 'multa']);
            $table->decimal('valor', 15, 2);
            $table->enum('tipo_valor', ['fixo', 'percentual', 'variavel'])->default('fixo');
            $table->decimal('percentual', 5, 2)->nullable();
            $table->string('unidade')->nullable(); // m2, ha, unidade, etc.
            $table->foreignId('conta_id')->constrained('contas_gerais')->onDelete('restrict');
            $table->boolean('activo')->default(true);
            $table->date('valido_de')->nullable();
            $table->date('valido_ate')->nullable();
            $table->timestamps();
            
            $table->index('codigo');
            $table->index('tipo');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxas_emolumentos');
    }
};
