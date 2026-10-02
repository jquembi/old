<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagamentos_financeiros', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique();
            $table->foreignId('lancamento_id')->nullable()->constrained('lancamentos')->onDelete('set null');
            $table->foreignId('taxa_emolumento_id')->nullable()->constrained('taxas_emolumentos')->onDelete('set null');
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('set null');
            $table->foreignId('municipio_id')->nullable()->constrained('municipios')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('data_pagamento');
            $table->decimal('valor', 15, 2);
            $table->enum('metodo_pagamento', ['dinheiro', 'transferencia', 'multicaixa', 'cheque', 'cartao']);
            $table->string('referencia')->nullable();
            $table->enum('status', ['pendente', 'confirmado', 'cancelado', 'reembolsado'])->default('pendente');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('numero');
            $table->index('data_pagamento');
            $table->index('status');
            $table->index('tenant_id');
            $table->index('municipio_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos_financeiros');
    }
};
