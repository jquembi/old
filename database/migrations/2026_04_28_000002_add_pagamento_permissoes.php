<?php

use Illuminate\Database\Migrations\Migration;

/**
 * As permissões RUPE são definidas pelo seeder fundamental de ACL.
 * Esta migration histórica deixa de inserir dados no processo de migrate.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Migrado para database/seeders/RolesPermissoesSeederV2.php.
    }

    public function down(): void
    {
        // Não remover permissões que possam estar associadas a papéis activos.
    }
};
