<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('croquis') && Schema::hasColumn('croquis', 'coordenadas_texto')) {
            if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                DB::statement('ALTER TABLE croquis MODIFY coordenadas_texto TEXT NULL');

                return;
            }

            Schema::table('croquis', function (Blueprint $table): void {
                $table->text('coordenadas_texto')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('croquis') && Schema::hasColumn('croquis', 'coordenadas_texto')) {
            if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                DB::statement('ALTER TABLE croquis MODIFY coordenadas_texto VARCHAR(255) NULL');

                return;
            }

            Schema::table('croquis', function (Blueprint $table): void {
                $table->string('coordenadas_texto')->nullable()->change();
            });
        }
    }
};
