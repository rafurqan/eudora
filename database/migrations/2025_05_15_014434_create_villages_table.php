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
        Schema::create('villages', function (Blueprint $table) {
            $table->string('id'); // KDDESA (contoh: 10 karakter)
            $table->string('sub_district_id'); // KDKEC
            $table->string('name');
            $table->string('kemendagri');
            $table->timestamps();

            $table->foreign('sub_district_id')
                ->references('id')
                ->on('sub_districts')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('villages');
    }
};

