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
        Schema::table('student_parents', function (Blueprint $table) {
            $table->dropColumn('occupation'); // drop string column
            $table->uuid('occupation_id')->nullable()->after('name');

            $table->foreign('occupation_id')->references('id')->on('occupations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_parents', function (Blueprint $table) {
            $table->dropForeign(['occupation_id']);
            $table->dropColumn('occupation_id');
            $table->string('occupation')->nullable(); // restore original string
        });
    }

};
