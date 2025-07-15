<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_parents', function (Blueprint $table) {
            $table->uuid('parent_type_id')->nullable()->after('student_id');
        });

        DB::table('student_parents')->get()->each(function ($row) {
            if ($row->parent_type) {
                $parentType = DB::table('parent_types')->where('name', $row->parent_type)->first();
                if ($parentType) {
                    DB::table('student_parents')
                        ->where('id', $row->id)
                        ->update(['parent_type_id' => $parentType->id]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_parents', function (Blueprint $table) {
            $table->dropColumn('parent_type_id');
        });
    }
};
