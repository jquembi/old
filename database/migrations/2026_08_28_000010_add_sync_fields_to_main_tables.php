<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'operacoes',
            'instrumentos',
            'loteamentos',
            'croquis',
            'pareceres',
            'projectos_sona',
            'outros_projectos',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->uuid('sync_id')->unique()->after('id');
                    $table->enum('sync_status', ['pending', 'synced', 'conflict'])->default('synced')->after('sync_id');
                    $table->integer('sync_version')->default(1)->after('sync_status');
                    $table->timestamp('synced_at')->nullable()->after('sync_version');
                    $table->text('sync_error')->nullable()->after('synced_at');
                    
                    $table->index('sync_id');
                    $table->index('sync_status');
                });

                // Gerar sync_id para registos existentes
                DB::table($table)->whereNull('sync_id')->get()->each(function ($record) use ($table) {
                    DB::table($table)
                        ->where('id', $record->id)
                        ->update(['sync_id' => (string) \Illuminate\Support\Str::uuid()]);
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'operacoes',
            'instrumentos',
            'loteamentos',
            'croquis',
            'pareceres',
            'projectos_sona',
            'outros_projectos',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn(['sync_id', 'sync_status', 'sync_version', 'synced_at', 'sync_error']);
                });
            }
        }
    }
};
