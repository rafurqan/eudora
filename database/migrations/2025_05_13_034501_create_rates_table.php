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
        Schema::create('rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_id');
            $table->json('child_ids')->nullable();
            $table->uuid('program_id')->nullable();
            $table->integer('price');
            $table->string('is_active', 1)->default('Y');
            $table->timestamp('created_at');
            $table->uuid('created_by_id');
            $table->timestamp('updated_at')->nullable();
            $table->uuid('updated_by_id')->nullable();

            // Foreign Key
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rates');
    }
};
