<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('projecto_sona') || Schema::hasColumn('projecto_sona', 'sync_id')) {
            return;
        }

        Schema::table('projecto_sona', function (Blueprint $table): void {
            $table->uuid('sync_id')->nullable()->unique()->after('id');
            $table->string('sync_status')->default('synced')->after('sync_id');
            $table->unsignedInteger('sync_version')->default(1)->after('sync_status');
            $table->timestamp('synced_at')->nullable()->after('sync_version');
            $table->text('sync_error')->nullable()->after('synced_at');
        });

        DB::table('projecto_sona')->whereNull('sync_id')->orderBy('id')->each(function ($project): void {
            DB::table('projecto_sona')->where('id', $project->id)->update([
                'sync_id' => (string) Str::uuid(),
            ]);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('projecto_sona') && Schema::hasColumn('projecto_sona', 'sync_id')) {
            Schema::table('projecto_sona', function (Blueprint $table): void {
                $table->dropColumn(['sync_id', 'sync_status', 'sync_version', 'synced_at', 'sync_error']);
            });
        }
    }
};
