<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SystemCoreSeeder extends Seeder
{
    /**
     * Dados fundamentais: catálogo territorial, ACL e tipos essenciais.
     * Não cria utilizadores, processos, pagamentos ou documentos demonstrativos.
     */
    public function run(): void
    {
        $this->call([
            ProvinciasMunicipiosSeeder::class,
            RolesPermissoesSeederV2::class,
            CategoriaInstrumentosSeeder::class,
            TipoDocumentoQrConfigSeeder::class,
            WorkflowsSeeder::class,
        ]);
    }
}
