<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'arquivos_base',
        'instrumentos',
        'loteamentos',
        'operacoes_urbanisticas',
        'croquis',
        'pareceres',
        'projecto_sona',
        'outros_projectos',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'deleted_by')) {
                    $table->foreignId('deleted_by')->nullable()->after('updated_at')->constrained('users')->nullOnDelete();
                }

                if (! Schema::hasColumn($tableName, 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'deleted_by')) {
                    $table->dropConstrainedForeignId('deleted_by');
                }

                if (Schema::hasColumn($tableName, 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }
};
