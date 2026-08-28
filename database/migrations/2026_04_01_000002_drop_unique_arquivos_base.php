<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the unique constraint so we can have multiple files of the same type per municipality
        // MySQL requires dropping the FK first when removing a shared index
        Schema::table('arquivos_base', function (Blueprint $table) {
            $table->dropForeign(['municipio_id']);
            $table->dropUnique(['municipio_id', 'tipo']);
            $table->foreign('municipio_id')->references('id')->on('municipios')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('arquivos_base', function (Blueprint $table) {
            $table->unique(['municipio_id', 'tipo']);
        });
    }
};
