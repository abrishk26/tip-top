<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name', 200);
            $table->string('email')->unique();
            $table->string('password_hash')->nullable();
            $table->string('phone')->nullable();
            $table->enum('role', ['user', 'admin'])->default('user');
            $table->boolean('is_active')->default(false);
            $table->boolean('email_verified')->default(false);
            $table->string('verification_token', 64)->nullable()->unique();
            $table->timestamp('verification_token_expires_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['email', 'is_active']);
            $table->index(['verification_token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
