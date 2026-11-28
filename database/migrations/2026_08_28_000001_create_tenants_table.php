<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // HUI, BGO, LUA, CTR...
            $table->string('name');
            $table->enum('type', ['central', 'provincial'])->default('provincial');
            $table->string('database_name');
            $table->string('database_host')->nullable();
            $table->integer('database_port')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('sync_status', ['online', 'offline', 'syncing'])->default('online');
            $table->timestamp('last_sync_at')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
            
            $table->index('type');
            $table->index('is_active');
            $table->index('sync_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
