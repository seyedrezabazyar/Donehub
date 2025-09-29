<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->text('bio')->nullable();
            $table->string('avatar_path', 500)->nullable();
            $table->string('cover_image_path', 500)->nullable();
            $table->string('website')->nullable();
            $table->enum('visibility', ['public', 'members_only', 'private'])->default('public');
            $table->boolean('show_achievements')->default(true);
            $table->boolean('show_statistics')->default(true);
            $table->bigInteger('total_points')->default(0);
            $table->integer('current_level')->default(1);
            $table->integer('reputation_score')->default(0);
            $table->string('referral_code', 20)->nullable()->unique();
            $table->timestamps();

            // Indexes
            $table->index('visibility');
            $table->index('total_points');
            $table->index('current_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
