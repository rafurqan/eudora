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
        Schema::table('grants', function (Blueprint $table) {
            $table->bigInteger('total_used_funds')->default(0)->after('total_funds');
            $table->integer('current_reset_version')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grants', function (Blueprint $table) {
            $table->dropColumn('total_used_funds');
            $table->dropColumn('current_reset_version');
        });
    }
};
