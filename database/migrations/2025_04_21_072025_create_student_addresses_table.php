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
        Schema::create('student_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('street');
            $table->uuid('student_id');
            $table->timestamps();
            $table->uuid('created_by_id');
            $table->uuid('updated_by_id')->nullable();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_addresses');
    }
};
