<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('municipios', function (Blueprint $table): void {
            if (! Schema::hasColumn('municipios', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('activo');
            }

            if (! Schema::hasColumn('municipios', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }

            if (! Schema::hasColumn('municipios', 'zoom_padrao')) {
                $table->unsignedTinyInteger('zoom_padrao')->default(12)->after('longitude');
            }

            if (! Schema::hasColumn('municipios', 'bbox')) {
                $table->json('bbox')->nullable()->after('zoom_padrao');
            }
        });
    }

    public function down(): void
    {
        Schema::table('municipios', function (Blueprint $table): void {
            foreach (['bbox', 'zoom_padrao', 'longitude', 'latitude'] as $column) {
                if (Schema::hasColumn('municipios', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
