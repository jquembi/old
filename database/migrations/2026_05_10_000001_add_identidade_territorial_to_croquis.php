<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('croquis', function (Blueprint $table) {
            $table->string('codigo_territorial')->nullable()->unique()->after('utm_northing');
            $table->decimal('centro_utm_easting', 12, 3)->nullable()->after('codigo_territorial');
            $table->decimal('centro_utm_northing', 13, 3)->nullable()->after('centro_utm_easting');
            $table->decimal('raio_protecao_m', 8, 3)->default(15)->after('centro_utm_northing');

            $table->index(['utm_zone', 'utm_hemisphere', 'centro_utm_easting', 'centro_utm_northing'], 'croquis_identidade_utm_idx');
        });
    }

    public function down(): void
    {
        Schema::table('croquis', function (Blueprint $table) {
            $table->dropIndex('croquis_identidade_utm_idx');
            $table->dropUnique(['codigo_territorial']);
            $table->dropColumn([
                'codigo_territorial',
                'centro_utm_easting',
                'centro_utm_northing',
                'raio_protecao_m',
            ]);
        });
    }
};
