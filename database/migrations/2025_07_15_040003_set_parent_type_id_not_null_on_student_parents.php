<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_parents', function (Blueprint $table) {
            $table->uuid('parent_type_id')->nullable(false)->change();

            $table->foreign('parent_type_id')
                ->references('id')
                ->on('parent_types')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('student_parents', function (Blueprint $table) {
            $table->dropForeign(['parent_type_id']);
            $table->uuid('parent_type_id')->nullable()->change();
        });
    }
};
