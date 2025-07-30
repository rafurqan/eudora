<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_origin_schools', function (Blueprint $table) {
            // Drop FK dan kolom lama
            $table->dropForeign(['education_level_id']);
            $table->dropColumn('education_level_id');

            // Tambahkan FK dan kolom baru
            $table->uuid('education_id')->nullable()->after('name'); // sesuaikan posisi jika perlu
            $table->foreign('education_id')->references('id')->on('educations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_origin_schools', function (Blueprint $table) {
            $table->dropForeign(['education_id']);
            $table->dropColumn('education_id');

            $table->uuid('education_level_id')->nullable()->after('name'); // sesuaikan posisi
            $table->foreign('education_level_id')->references('id')->on('education_levels')->nullOnDelete();
        });
    }

};
