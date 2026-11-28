<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use RuntimeException;

class DevelopmentDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('DevelopmentDemoSeeder não pode ser executado em produção.');
        }

        $this->call(UsuariosIniciaisSeederLegado::class);
    }
}
