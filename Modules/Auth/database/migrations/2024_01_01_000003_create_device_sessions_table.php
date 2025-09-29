<?php

// Modules/Auth/Database/Migrations/2024_01_01_000002_create_device_sessions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('device_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('device_id', 255); // hash of device fingerprint
            $table->string('device_name', 255)->nullable();
            $table->enum('device_type', ['mobile', 'tablet', 'desktop'])->default('desktop');
            $table->string('browser', 100)->nullable();
            $table->string('browser_version', 50)->nullable();
            $table->string('platform', 100)->nullable();
            $table->string('platform_version', 50)->nullable();
            $table->ipAddress('ip_address');
            $table->json('location')->nullable();
            $table->boolean('is_trusted')->default(false);
            $table->timestamp('last_activity');
            $table->timestamps();

            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Unique constraint - هر device_id فقط یک بار برای هر user
            $table->unique(['user_id', 'device_id']);

            // Indexes
            $table->index('user_id');
            $table->index('device_id');
            $table->index('last_activity');
            $table->index(['user_id', 'last_activity']);
            $table->index(['user_id', 'is_trusted']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('device_sessions');
    }
};
