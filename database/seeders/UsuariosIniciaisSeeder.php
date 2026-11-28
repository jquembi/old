<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Alias histórico. A execução é sempre explícita e limitada a desenvolvimento.
 */
class UsuariosIniciaisSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('Este seeder de demonstração está bloqueado em produção.');
        }

        $this->call(DevelopmentDemoSeeder::class);
    }
}
