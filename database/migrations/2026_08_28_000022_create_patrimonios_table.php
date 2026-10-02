<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrimonios', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->foreignId('categoria_patrimonio_id')->nullable()->constrained('categorias_patrimonio')->onDelete('set null');
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('set null');
            $table->foreignId('municipio_id')->nullable()->constrained('municipios')->onDelete('set null');
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->onDelete('set null');
            $table->foreignId('localizacao_id')->nullable()->constrained('localizacoes')->onDelete('set null');
            $table->date('data_aquisicao');
            $table->decimal('valor_aquisicao', 15, 2);
            $table->decimal('valor_atual', 15, 2)->nullable();
            $table->enum('estado', ['novo', 'bom', 'razoavel', 'mau', 'danificado', 'obsoleto'])->default('bom');
            $table->enum('status', ['ativo', 'inativo', 'manutencao', 'baixa', 'alienado'])->default('ativo');
            $table->string('numero_serie')->nullable();
            $table->string('fornecedor')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('codigo');
            $table->index('estado');
            $table->index('status');
            $table->index('tenant_id');
            $table->index('categoria_patrimonio_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrimonios');
    }
};
