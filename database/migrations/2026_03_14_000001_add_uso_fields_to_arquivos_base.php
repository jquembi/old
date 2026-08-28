<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arquivos_base', function (Blueprint $table) {
            $table->foreignId('em_uso_por')->nullable()->after('enviado_por')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('em_uso_desde')->nullable()->after('em_uso_por');
        });
    }

    public function down(): void
    {
        Schema::table('arquivos_base', function (Blueprint $table) {
            $table->dropForeign(['em_uso_por']);
            $table->dropColumn(['em_uso_por', 'em_uso_desde']);
        });
    }
};
