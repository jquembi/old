<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'instrumento_ficheiros' => 'enviado_por',
            'loteamento_ficheiros'  => 'enviado_por',
            'projecto_sona_ficheiros' => 'enviado_por',
            'pareceres'             => 'criado_por',
            'croquis'               => 'criado_por',
            'arquivos_base'         => 'enviado_por',
        ];

        foreach ($tables as $tableName => $afterColumn) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName, $afterColumn) {
                if (! Schema::hasColumn($tableName, 'qr_token')) {
                    $table->string('qr_token')->unique()->nullable()->after($afterColumn);
                }
                if (! Schema::hasColumn($tableName, 'verificado')) {
                    $table->boolean('verificado')->default(false)->after('qr_token');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'instrumento_ficheiros',
            'loteamento_ficheiros',
            'projecto_sona_ficheiros',
            'pareceres',
            'croquis',
            'arquivos_base',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (Schema::hasColumn($table->getTable(), 'qr_token')) {
                        $table->dropUnique(['qr_token']);
                        $table->dropColumn(['qr_token', 'verificado']);
                    }
                });
            }
        }
    }
};
