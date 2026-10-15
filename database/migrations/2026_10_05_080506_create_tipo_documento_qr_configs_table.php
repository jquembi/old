<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tipo_documento_qr_configs', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_documento', 60)->unique()->comment('Chave única: croqui, memoria_descritiva, instrumento, loteamento, parecer, sona, outro_projeto, carta_municipal');
            $table->string('nome', 120);
            $table->text('descricao')->nullable();
            $table->string('formato_papel', 20)->default('A4')->comment('A4, A3, personalizado');
            $table->string('orientacao', 20)->default('retrato')->comment('retrato, paisagem');
            $table->string('pagina', 20)->default('todas')->comment('primeira, ultima, todas');
            
            // Posição em percentual relativo (0 a 100%) para adaptação perfeita a qualquer proporção/DPI
            $table->decimal('posicao_x_pct', 5, 2)->default(80.00)->comment('Posição X em percentagem da largura da página (0 a 100%)');
            $table->decimal('posicao_y_pct', 5, 2)->default(5.00)->comment('Posição Y em percentagem da altura da página (0 a 100%)');
            
            // Tamanho em percentual ou milímetros
            $table->decimal('largura_pct', 5, 2)->default(15.00)->comment('Largura do QR em percentagem da largura da página (ex: 15%)');
            $table->decimal('tamanho_mm', 5, 2)->default(28.00)->comment('Tamanho estimado em milímetros para exportação PDF/impressão');
            
            // Estilização visual do QR Code
            $table->string('cor_qr', 20)->default('#18181b');
            $table->string('cor_fundo', 20)->default('#ffffff');
            $table->boolean('incluir_moldura')->default(true);
            $table->string('cor_moldura', 20)->default('#e4e4e7');
            $table->boolean('incluir_rotulo')->default(true);
            $table->string('texto_rotulo', 100)->default('AUTENTICIDADE • QR CODE');
            $table->string('nivel_correcao', 5)->default('M')->comment('L, M, Q, H');
            
            $table->boolean('ativo')->default(true);
            $table->foreignId('atualizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_documento_qr_configs');
    }
};
