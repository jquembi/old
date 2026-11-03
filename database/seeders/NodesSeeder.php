<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class NodesSeeder extends Seeder
{
    public function run(): void
    {
        $nodes = [
            ['code' => 'CUN', 'name' => 'Província do Cunene', 'database_name' => 'gterra_cunene'],
            ['code' => 'HUI', 'name' => 'Província da Huíla', 'database_name' => 'gterra_huila'],
            ['code' => 'NAM', 'name' => 'Província do Namibe', 'database_name' => 'gterra_namibe'],
        ];

        foreach ($nodes as $node) {
            Tenant::firstOrCreate(
                ['code' => $node['code']],
                [
                    'name' => $node['name'],
                    'type' => Tenant::TYPE_PROVINCIAL,
                    'database_name' => $node['database_name'],
                    'is_active' => true,
                    'sync_status' => Tenant::SYNC_ONLINE,
                ]
            );
        }
    }
}
