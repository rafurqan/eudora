<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEntryYearToStudentsAndProspectiveStudentsTable extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('entry_year', 4)->nullable()->after('photo_filename');
        });

        Schema::table('prospective_students', function (Blueprint $table) {
            $table->string('entry_year', 4)->nullable()->after('photo_filename');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('entry_year');
        });

        Schema::table('prospective_students', function (Blueprint $table) {
            $table->dropColumn('entry_year');
        });
    }
}
