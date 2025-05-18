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
        Schema::create('student_origin_schools', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('aggregate_id');
            $table->string('aggregate_type');
            $table->string('school_name');
            $table->string('graduation_year');
            $table->uuid('school_type_id');
            $table->string('npsn')->nullable();
            $table->string('address_name')->nullable();
            $table->uuid('education_level_id')->nullable();


            $table->timestamps();
            $table->uuid('created_by_id');
            $table->uuid('updated_by_id')->nullable();


            $table->foreign('school_type_id')->references('id')->on('school_types')->onDelete('restrict');
            $table->foreign('education_level_id')->references('id')->on('education_levels')->onDelete('set null');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_origin_schools');
    }
};
