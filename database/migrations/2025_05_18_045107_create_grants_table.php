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
        Schema::create('grants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('donor_id');
            $table->uuid('donation_type_id');
            $table->string('is_active', 1);
            $table->string('description');
            $table->decimal('total_funds', 15, 2);
            $table->date('grant_expiration_date');
            $table->string('notes')->nullable();
            $table->uuid('created_by_id');
            $table->uuid('updated_by_id')->nullable();
            $table->timestamps(); // created_at = date received

            $table->foreign('donor_id')->references('id')->on('donors')->onDelete('restrict');
            $table->foreign('donation_type_id')->references('id')->on('donation_types')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grants');
    }
};
