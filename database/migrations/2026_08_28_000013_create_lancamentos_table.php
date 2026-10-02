<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lancamentos', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique();
            $table->date('data');
            $table->foreignId('conta_debito_id')->constrained('contas_gerais')->onDelete('restrict');
            $table->foreignId('conta_credito_id')->constrained('contas_gerais')->onDelete('restrict');
            $table->decimal('valor', 15, 2);
            $table->text('descricao');
            $table->enum('tipo', ['receita', 'despesa', 'transferencia']);
            $table->enum('status', ['pendente', 'confirmado', 'cancelado'])->default('pendente');
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('set null');
            $table->foreignId('municipio_id')->nullable()->constrained('municipios')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('numero');
            $table->index('data');
            $table->index('tipo');
            $table->index('status');
            $table->index('tenant_id');
            $table->index('municipio_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lancamentos');
    }
};
