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
        Schema::create('verification_tokens', function (Blueprint $table) {
            $table->ulid('id')->primary();          // ULID for token itself
            $table->string('token', 64)->unique();          // Unique token
            $table->ulid('tokenable_id');          // ULID of the entity
            $table->string('tokenable_type');          // Entity type (User, Employee, etc.)
            $table->dateTime('expires_at');            // Expiration time
            $table->timestamps();                       // created_at and updated_at

            $table->unique(['tokenable_type', 'tokenable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_tokens');
    }
};
