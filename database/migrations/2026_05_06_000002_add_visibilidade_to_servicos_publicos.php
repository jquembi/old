<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['operacoes_urbanisticas', 'croquis', 'pareceres'] as $tabela) {
            if (Schema::hasTable($tabela) && ! Schema::hasColumn($tabela, 'visibilidade')) {
                Schema::table($tabela, function (Blueprint $table) {
                    $table->string('visibilidade', 20)->default('privado')->after('criado_por');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['operacoes_urbanisticas', 'croquis', 'pareceres'] as $tabela) {
            if (Schema::hasTable($tabela) && Schema::hasColumn($tabela, 'visibilidade')) {
                Schema::table($tabela, function (Blueprint $table) {
                    $table->dropColumn('visibilidade');
                });
            }
        }
    }
};
