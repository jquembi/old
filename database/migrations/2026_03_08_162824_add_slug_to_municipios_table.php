<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('municipios', function (Blueprint $table) {
            if (!Schema::hasColumn('municipios', 'slug')) {
                $table->string('slug')->default('')->after('nome');
            }
        });

        // Gerar slugs para registos existentes (apenas se slug estiver vazio)
        foreach (\App\Models\Municipio::all() as $municipio) {
            if (empty($municipio->slug)) {
                $municipio->slug = \Illuminate\Support\Str::slug($municipio->nome);
                $municipio->saveQuietly();
            }
        }

        Schema::table('municipios', function (Blueprint $table) {
            if (Schema::hasColumn('municipios', 'slug')) {
                $table->string('slug')->unique()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('municipios', function (Blueprint $table) {
            if (Schema::hasColumn('municipios', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};
