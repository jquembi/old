<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisicoes_compra', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique();
            $table->date('data');
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('set null');
            $table->foreignId('municipio_id')->nullable()->constrained('municipios')->onDelete('set null');
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->onDelete('set null');
            $table->foreignId('solicitante_id')->nullable()->constrained('funcionarios')->onDelete('set null');
            $table->foreignId('aprovador_id')->nullable()->constrained('funcionarios')->onDelete('set null');
            $table->text('descricao');
            $table->enum('prioridade', ['baixa', 'normal', 'alta', 'urgente'])->default('normal');
            $table->enum('status', ['rascunho', 'pendente_aprovacao', 'aprovado', 'rejeitado', 'em_processamento', 'concluido', 'cancelado'])->default('rascunho');
            $table->decimal('valor_estimado', 15, 2)->nullable();
            $table->foreignId('lancamento_id')->nullable()->constrained('lancamentos')->onDelete('set null');
            $table->text('observacoes')->nullable();
            $table->timestamp('aprovado_em')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('numero');
            $table->index('data');
            $table->index('status');
            $table->index('prioridade');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisicoes_compra');
    }
};
