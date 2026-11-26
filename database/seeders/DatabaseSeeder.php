<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProvinciasMunicipiosSeeder::class,
            RolesPermissoesSeederV2::class,
            UsuariosIniciaisSeeder::class,
            CategoriaInstrumentosSeeder::class,
            // ArquivosBaseTesteSeeder::class, // Descomente para criar uploads de teste
        ]);
    }
}
