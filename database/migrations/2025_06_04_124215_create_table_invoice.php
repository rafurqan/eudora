<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_class')->nullable();
            $table->uuid('entity_id');
            $table->string('entity_type');
            $table->index(['entity_id', 'entity_type']);
            $table->string('code')->nullable();
            $table->timestamp('publication_date')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->string('notes', 255)->nullable();
            $table->string('status')->nullable();
            $table->integer('total');
            $table->boolean('delivered_wa')->default(false);
            $table->timestamps();
            $table->uuid('created_by_id')->nullable();
            $table->uuid('updated_by_id')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};
