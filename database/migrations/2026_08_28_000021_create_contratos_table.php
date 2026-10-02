<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique();
            $table->foreignId('fornecedor_id')->constrained('fornecedores')->onDelete('restrict');
            $table->foreignId('requisicao_compra_id')->nullable()->constrained('requisicoes_compra')->onDelete('set null');
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('set null');
            $table->foreignId('municipio_id')->nullable()->constrained('municipios')->onDelete('set null');
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->text('descricao');
            $table->decimal('valor_total', 15, 2);
            $table->enum('tipo', ['compra_bens', 'prestacao_servicos', 'obra', 'consultoria'])->default('prestacao_servicos');
            $table->enum('status', ['rascunho', 'pendente_assinatura', 'ativo', 'suspenso', 'concluido', 'cancelado'])->default('rascunho');
            $table->string('arquivo_path')->nullable();
            $table->text('condicoes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('numero');
            $table->index('data_inicio');
            $table->index('data_fim');
            $table->index('status');
            $table->index('tipo');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
