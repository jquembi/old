<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installation_metadata', function (Blueprint $table) {
            $table->id();
            $table->string('status', 32)->default('in_progress');
            $table->unsignedSmallInteger('completed_step')->default(0);
            $table->string('app_version', 40)->nullable();
            $table->string('db_connection', 32);
            $table->string('database_name', 255);
            $table->string('deployment_mode', 20);
            $table->string('tenant_code', 10);
            $table->string('admin_email')->nullable();
            $table->json('configuration')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installation_metadata');
    }
};
