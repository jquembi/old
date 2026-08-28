<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('croquis')) {
            return;
        }

        Schema::table('croquis', function (Blueprint $table) {
            if (! Schema::hasColumn('croquis', 'memoria_descritiva_caminho')) {
                $table->string('memoria_descritiva_caminho')->nullable()->after('ficheiro_pdf_nome');
            }

            if (! Schema::hasColumn('croquis', 'memoria_descritiva_nome')) {
                $table->string('memoria_descritiva_nome')->nullable()->after('memoria_descritiva_caminho');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('croquis')) {
            return;
        }

        Schema::table('croquis', function (Blueprint $table) {
            foreach (['memoria_descritiva_nome', 'memoria_descritiva_caminho'] as $column) {
                if (Schema::hasColumn('croquis', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
