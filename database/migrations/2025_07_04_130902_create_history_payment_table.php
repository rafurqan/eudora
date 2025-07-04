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
        Schema::create('history_payment', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('payment_id')->nullable();
            $table->uuid('invoice_id')->nullable();

            $table->string('code')->nullable();
            $table->string('payment_method')->nullable();
            $table->integer('nominal_payment')->nullable();
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();

            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('reference_number')->nullable();

            $table->uuid('id_grant')->nullable();
            $table->integer('grant_amount')->nullable();
            $table->string('status')->nullable();

            $table->uuid('deleted_by_id')->nullable();
            $table->text('deleted_reason')->nullable();
            $table->timestamp('deleted_at')->useCurrent();

            $table->uuid('original_created_by_id')->nullable();
            $table->timestamp('original_created_at')->nullable();
            $table->uuid('original_updated_by_id')->nullable();
            $table->timestamp('original_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_payment');
    }
};
