<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('username', 50)->unique()->nullable();
            $table->timestamp('username_last_changed_at')->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone', 20)->unique()->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('national_id', 20)->nullable();
            $table->string('password')->nullable();
            $table->enum('preferred_method', ['password', 'otp'])->default('password');
            $table->unsignedInteger('failed_attempts')->default(0);
            $table->timestamp('locked_until')->nullable()->index();
            $table->timestamp('last_login_at')->nullable();
            $table->string('avatar')->nullable();

            // فیلدهای پروفایل - اصلاح شده
            $table->foreignId('province_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('city_id')->nullable()->constrained()->onDelete('set null');
            $table->text('address')->nullable(); // اضافه شد
            $table->boolean('is_admin')->default(false)->index();

            $table->rememberToken();
            $table->timestamps();

            // Indexes
            $table->index(['email_verified_at', 'phone_verified_at'], 'user_verification_index');
            $table->index(['failed_attempts', 'locked_until'], 'user_security_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
