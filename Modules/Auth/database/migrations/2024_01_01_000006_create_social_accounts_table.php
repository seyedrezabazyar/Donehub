<?php

// Modules/Auth/Database/Migrations/2024_01_01_000004_create_social_accounts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('provider', 50); // google, facebook, etc
            $table->string('provider_id', 255); // ID from provider
            $table->string('name', 255)->nullable();
            $table->string('email', 320)->nullable();
            $table->string('avatar', 2048)->nullable();
            $table->text('token')->nullable(); // encrypted access token
            $table->text('refresh_token')->nullable(); // encrypted refresh token
            $table->timestamp('expires_at')->nullable();
            $table->json('raw_data')->nullable(); // raw response from provider
            $table->timestamps();

            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Unique constraint - هر provider فقط یک بار برای هر user
            $table->unique(['user_id', 'provider']);

            // Unique constraint - هر provider_id فقط یک بار برای هر provider
            $table->unique(['provider', 'provider_id']);

            // Indexes
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('social_accounts');
    }
};
