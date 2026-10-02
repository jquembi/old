<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fornecedores', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nome');
            $table->string('nif', 15)->nullable();
            $table->string('telefone');
            $table->string('email')->nullable();
            $table->text('endereco')->nullable();
            $table->foreignId('municipio_id')->nullable()->constrained('municipios')->onDelete('set null');
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('set null');
            $table->enum('tipo', ['bens', 'servicos', 'ambos'])->default('ambos');
            $table->boolean('activo')->default(true);
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('codigo');
            $table->index('nif');
            $table->index('tipo');
            $table->index('activo');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fornecedores');
    }
};
