<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Limpar e prevenir constraints duplicadas na tabela operacoes_urbanisticas
     * Esta migração deve ser que remove as antigas que causam conflito
     */
    public function up(): void
    {
        // Apenas para MySQL/MariaDB
        if (!in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            return;
        }

        if (!Schema::hasTable('operacoes_urbanisticas')) {
            return;
        }

        try {
            // Remover todas as foreign keys duplicadas para 'aprovado_por'
            $constraints = DB::select("
                SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = 'operacoes_urbanisticas'
                AND TABLE_SCHEMA = DATABASE()
                AND CONSTRAINT_NAME LIKE '%aprovado_por%'
                AND CONSTRAINT_NAME != 'PRIMARY'
            ");

            foreach ($constraints as $constraint) {
                try {
                    DB::statement("ALTER TABLE operacoes_urbanisticas DROP FOREIGN KEY {$constraint->CONSTRAINT_NAME}");
                } catch (\Exception $e) {
                    // Ignorar se não existe
                }
            }
        } catch (\Exception $e) {
            // Ignorar qualquer erro
        }
    }

    public function down(): void
    {
        // Nada a fazer
    }
};
