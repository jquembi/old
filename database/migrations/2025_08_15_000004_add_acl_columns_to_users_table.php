<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles')->nullOnDelete();
            $table->foreignId('provincia_id')->nullable()->after('role_id')->constrained('provincias')->nullOnDelete();
            $table->foreignId('municipio_id')->nullable()->after('provincia_id')->constrained('municipios')->nullOnDelete();
            $table->boolean('activo')->default(true)->after('email');
            $table->foreignId('criado_por')->nullable()->after('activo')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['provincia_id']);
            $table->dropForeign(['municipio_id']);
            $table->dropForeign(['criado_por']);
            $table->dropColumn(['role_id', 'provincia_id', 'municipio_id', 'activo', 'criado_por']);
        });
    }
};
