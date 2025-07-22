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
        Schema::table('invoice_item', function (Blueprint $table) {
            $table->foreign('rate_id')
                  ->references('id')->on('rates')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_item', function (Blueprint $table) {
            $table->dropForeign(['rate_id']);
        });
    }
};
