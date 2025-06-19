<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('registration_code_reservations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('registration_code')->unique();
            $table->timestamp('reserved_at');
            $table->boolean('used')->default(false);

            $table->uuid('created_by_id')->nullable();
            $table->foreign('created_by_id')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_code_reservations');
    }
};
