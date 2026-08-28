<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remover constraints duplicadas se existirem
        // Esta migração preserva dados e remove conflitos de migrações antigas

        // Verificar se a tabela operacoes_urbanisticas existe
        if (Schema::hasTable('operacoes_urbanisticas')) {
            // Remover constraint duplicada se existir
            try {
                $connection = DB::connection()->getDriverName();

                if ($connection === 'mysql') {
                    // Verificar e remover foreign key duplicada
                    $sql = "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                            WHERE TABLE_NAME = 'operacoes_urbanisticas'
                            AND COLUMN_NAME = 'aprovado_por'";

                    $constraints = DB::select($sql);

                    foreach ($constraints as $constraint) {
                        DB::statement("ALTER TABLE operacoes_urbanisticas DROP FOREIGN KEY {$constraint->CONSTRAINT_NAME}");
                    }
                }
            } catch (\Exception $e) {
                // Silenciosamente ignorar se não conseguir remover
            }
        }
    }

    public function down(): void
    {
        // Nada a fazer no rollback
    }
};
