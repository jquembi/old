<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona o estado de publicação ao croqui.
 *
 * Fluxo obrigatório:
 *   1. Técnico faz upload do PDF e guarda o croqui (publicado = false)
 *   2. Administrador publica o croqui (publicado = true)
 *   3. Só após publicação é possível registar o pagamento RUP
 *   4. Após RUP pago, administrador homologa o download (download_liberado = true)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('croquis', function (Blueprint $table) {
            if (! Schema::hasColumn('croquis', 'publicado')) {
                $table->boolean('publicado')->default(false)->after('verificado');
            }
            if (! Schema::hasColumn('croquis', 'publicado_por')) {
                $table->foreignId('publicado_por')->nullable()->constrained('users')->nullOnDelete()->after('publicado');
            }
            if (! Schema::hasColumn('croquis', 'publicado_em')) {
                $table->timestamp('publicado_em')->nullable()->after('publicado_por');
            }
        });
    }

    public function down(): void
    {
        Schema::table('croquis', function (Blueprint $table) {
            $table->dropConstrainedForeignId('publicado_por');
            $table->dropColumn(['publicado', 'publicado_em']);
        });
    }
};
