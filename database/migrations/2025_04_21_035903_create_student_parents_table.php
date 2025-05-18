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
        Schema::create('student_parents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('aggregate_id');
            $table->string('aggregate_type');
            $table->string('parent_type');
            $table->string('full_name');
            $table->string('nik')->nullable();
            $table->year('birth_year')->nullable();
            $table->foreignUuid('education_level_id')->nullable()->constrained()->nullOnDelete();
            $table->string('occupation')->nullable();
            $table->foreignUuid('income_range_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_emergency_contact')->default(false);
            $table->boolean('is_main_contact')->default(false);
            $table->timestamps();
            $table->uuid('created_by_id');
            $table->uuid('updated_by_id')->nullable();

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_parents');
    }
};
