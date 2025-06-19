<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('class_memberships', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('student_class_id');
            $table->uuid('student_id')->nullable();
            $table->uuid('prospective_student_id')->nullable();

            $table->string('reason')->nullable();

            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();

            $table->timestamps();
            $table->uuid('created_by')->nullable();

            // Foreign keys
            $table->foreign('student_class_id')->references('id')->on('student_classes')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('prospective_student_id')->references('id')->on('prospective_students')->onDelete('cascade');

            // Unique constraint untuk mencegah duplikat dalam periode yang sama
            $table->unique(['student_class_id', 'student_id', 'start_at']);
            $table->unique(['student_class_id', 'prospective_student_id', 'start_at']);
        });

        // Check constraint untuk PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("
                ALTER TABLE class_memberships
                ADD CONSTRAINT only_one_student_check
                CHECK (
                    (student_id IS NOT NULL AND prospective_student_id IS NULL)
                    OR (student_id IS NULL AND prospective_student_id IS NOT NULL)
                )
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('class_memberships');
    }
};
