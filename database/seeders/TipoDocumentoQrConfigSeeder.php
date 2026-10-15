<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoDocumentoQrConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (\App\Models\TipoDocumentoQrConfig::tiposPredefinidos() as $tipo => $config) {
            \App\Models\TipoDocumentoQrConfig::updateOrCreate(
                ['tipo_documento' => $tipo],
                array_merge($config, ['ativo' => true])
            );
        }
    }
}
