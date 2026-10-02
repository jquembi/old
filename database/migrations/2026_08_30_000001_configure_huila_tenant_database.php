<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $database = env('TENANT_HUILA_DATABASE');

        if (! $database && app()->environment(['local', 'testing'])) {
            $centralConnection = config('tenancy.database.central_connection');
            $database = config("database.connections.{$centralConnection}.database");
        }

        if ($database) {
            DB::table('tenants')->where('code', 'HUI')->update([
                'database_name' => $database,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // A ligação anterior depende do ambiente e não deve ser inferida ou apagada.
    }
};
