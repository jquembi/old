<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove constraints duplicadas na tabela operacoes
     * Resolva o error: "Duplicate foreign key constraint name 'operacoes_aprovado_por_foreign'"
     */
    public function up(): void
    {
        // Apenas para MySQL/MariaDB em produção
        if (DB::getDriverName() !== 'mysql' && DB::getDriverName() !== 'mariadb') {
            return;
        }

        // Remover todas as constraints de 'aprovado_por' existentes
        try {
            $table = 'operacoes_urbanisticas';
            
            // Buscar constraints existentes
            $constraints = DB::select(
                "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                 WHERE TABLE_NAME = ? AND COLUMN_NAME = 'aprovado_por' 
                 AND TABLE_SCHEMA = DATABASE()",
                [$table]
            );

            // Remover cada constraint duplicada
            foreach ($constraints as $constraint) {
                if ($constraint->CONSTRAINT_NAME !== 'PRIMARY') {
                    try {
                        DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$constraint->CONSTRAINT_NAME}");
                    } catch (\Exception $e) {
                        // Ignorar se não conseguir remover
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignorar erros
        }
    }

    public function down(): void
    {
        // Nada a fazer
    }
};
