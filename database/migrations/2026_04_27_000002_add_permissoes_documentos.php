<?php

use Illuminate\Database\Migrations\Migration;

/**
 * As permissões de documentos pertencem ao catálogo ACL semeado pelo
 * SystemCoreSeeder. Mantém-se esta migration histórica para instalações
 * existentes, sem inserir nem remover dados durante migrate.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Migrado para database/seeders/RolesPermissoesSeederV2.php.
    }

    public function down(): void
    {
        // Não remover permissões potencialmente utilizadas por utilizadores.
    }
};
