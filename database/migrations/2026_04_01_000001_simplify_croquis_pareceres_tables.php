<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('croquis', function (Blueprint $table) {
            if (!Schema::hasColumn('croquis', 'titulo')) {
                $table->string('titulo')->nullable()->after('tipo');
            }
            if (!Schema::hasColumn('croquis', 'slug')) {
                $table->string('slug')->nullable()->after('titulo');
            }
            if (!Schema::hasColumn('croquis', 'utm_zone')) {
                $table->string('utm_zone')->nullable()->after('longitude');
            }
            if (!Schema::hasColumn('croquis', 'utm_hemisphere')) {
                $table->string('utm_hemisphere')->nullable()->after('utm_zone');
            }
            if (!Schema::hasColumn('croquis', 'utm_easting')) {
                $table->decimal('utm_easting', 12, 3)->nullable()->after('utm_hemisphere');
            }
            if (!Schema::hasColumn('croquis', 'utm_northing')) {
                $table->decimal('utm_northing', 12, 3)->nullable()->after('utm_easting');
            }
        });
    }

    public function down(): void
    {
        Schema::table('croquis', function (Blueprint $table) {
            $columns = ['titulo', 'slug', 'utm_zone', 'utm_hemisphere', 'utm_easting', 'utm_northing'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('croquis', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
