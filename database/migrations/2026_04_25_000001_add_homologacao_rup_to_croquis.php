<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('croquis', function (Blueprint $table) {
            if (!Schema::hasColumn('croquis', 'rup_pago')) {
                $table->boolean('rup_pago')->default(false)->after('ficheiro_pdf_nome');
            }
            if (!Schema::hasColumn('croquis', 'rup_referencia')) {
                $table->string('rup_referencia')->nullable()->after('rup_pago');
            }
            if (!Schema::hasColumn('croquis', 'rup_pago_em')) {
                $table->timestamp('rup_pago_em')->nullable()->after('rup_referencia');
            }
            if (!Schema::hasColumn('croquis', 'download_liberado')) {
                $table->boolean('download_liberado')->default(false)->after('rup_pago_em');
            }
            if (!Schema::hasColumn('croquis', 'liberado_por')) {
                $table->foreignId('liberado_por')->nullable()->after('download_liberado')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('croquis', 'liberado_em')) {
                $table->timestamp('liberado_em')->nullable()->after('liberado_por');
            }
            if (!Schema::hasColumn('croquis', 'liberacao_observacao')) {
                $table->text('liberacao_observacao')->nullable()->after('liberado_em');
            }
        });
    }

    public function down(): void
    {
        Schema::table('croquis', function (Blueprint $table) {
            if (Schema::hasColumn('croquis', 'liberado_por')) {
                $table->dropConstrainedForeignId('liberado_por');
            }

            $columns = [
                'rup_pago',
                'rup_referencia',
                'rup_pago_em',
                'download_liberado',
                'liberado_em',
                'liberacao_observacao',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('croquis', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
