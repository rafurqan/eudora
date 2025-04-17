<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('education_levels', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('description');
            $table->string('level');
            $table->string('status');
            $table->string('created_by_id');
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_levels');
    }
};
