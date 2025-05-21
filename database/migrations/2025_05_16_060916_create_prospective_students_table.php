<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prospective-students', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('registration_code')->unique();
            $table->string('full_name');
            $table->string('nickname');
            $table->foreignUuid('religion_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('gender', ['male', 'female']);

            $table->string('birth_place');
            $table->enum('status', ['waiting', 'approved', 'rejected']);
            $table->date('birth_date');
            $table->string('nisn')->nullable();

            $table->foreignUuid('nationality_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('child_order')->nullable();
            $table->string('family_status')->nullable();
            $table->string('street')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            $table->string('village_id')->nullable();

            $table->foreignUuid('special_need_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('special_condition_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('transportation_mode_id')->nullable()->constrained()->nullOnDelete();
            $table->string('photo_filename')->nullable();
            $table->longText('health_condition')->nullable();
            $table->longText('hobby')->nullable();
            $table->longText('special_need')->nullable();
            $table->longText('additional_information')->nullable();
            $table->boolean('has_kip')->default(false);
            $table->boolean('has_kps')->default(false);
            $table->boolean('eligible_for_kip')->default(false);

            $table->timestamps();
            $table->uuid('created_by_id');
            $table->uuid('updated_by_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospective-students');
    }
};
