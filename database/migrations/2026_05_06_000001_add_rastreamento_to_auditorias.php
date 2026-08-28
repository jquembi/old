<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auditorias', function (Blueprint $table) {
            $table->string('request_id', 64)->nullable()->after('ip_address')->index();
            $table->string('method', 12)->nullable()->after('request_id');
            $table->text('url')->nullable()->after('method');
            $table->string('route_name')->nullable()->after('url')->index();
            $table->text('user_agent')->nullable()->after('route_name');
            $table->string('session_id', 120)->nullable()->after('user_agent');
            $table->json('contexto')->nullable()->after('session_id');
        });
    }

    public function down(): void
    {
        Schema::table('auditorias', function (Blueprint $table) {
            $table->dropIndex(['request_id']);
            $table->dropIndex(['route_name']);
            $table->dropColumn([
                'request_id',
                'method',
                'url',
                'route_name',
                'user_agent',
                'session_id',
                'contexto',
            ]);
        });
    }
};
