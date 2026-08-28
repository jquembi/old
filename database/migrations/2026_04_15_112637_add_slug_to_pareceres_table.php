<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pareceres', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('titulo');
            // Se quiser que o slug seja único:
            // $table->string('slug')->unique()->after('titulo');
        });
    }

    public function down()
    {
        Schema::table('pareceres', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
