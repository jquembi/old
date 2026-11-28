<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Esta antiga lista provisória de municípios da Huíla foi substituída pelo
 * catálogo territorial mantido em ProvinciasMunicipiosSeeder. Mantém-se a
 * migration apenas para compatibilidade com instalações que ainda não a
 * tenham registado; não introduz dados desactualizados durante migrate.
 */
return new class extends Migration
{
    public function up(): void
    {
        // O catálogo vigente é carregado por SystemCoreSeeder.
    }

    public function down(): void
    {
        // Não apagar municípios que possam estar referenciados em registos.
    }
};
