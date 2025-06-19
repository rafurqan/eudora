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
        Schema::table('rates', function (Blueprint $table) {
            $table->string('code', 100)->nullable();
            $table->string('category', 100)->nullable();
            $table->string('frequency', 100)->nullable();
            $table->string('applies_to', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rates', function (Blueprint $table) {
            $table->dropColumn(['code', 'category', 'frequency', 'applies_to', 'description']);
        });
    }
};
