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
        Schema::create('employees', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->boolean('is_active')->default(false);
            $table->ulid('tip_code');
            $table->ulid('service_provider_id');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->foreign('service_provider_id')->references('id')->on('service_providers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
