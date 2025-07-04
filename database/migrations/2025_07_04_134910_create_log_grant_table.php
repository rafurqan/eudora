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
        Schema::create('log_grant', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('grant_id');
            $table->uuid('payment_id'); 
            $table->bigInteger('amount_used');
            $table->string('period')->nullable(); 
            $table->uuid('created_by_id')->nullable();
            $table->timestamp('used_at')->useCurrent();
            $table->integer('reset_version')->default(1);

            $table->foreign('grant_id')->references('id')->on('grants');
            $table->foreign('payment_id')->references('id')->on('payment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_grant');
    }
};
