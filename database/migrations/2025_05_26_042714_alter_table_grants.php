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
            $table->dropForeign(['donor_id']);
            $table->dropForeign(['donation_type_id']);

            $table->renameColumn('donor_id', 'donor_name');
            $table->renameColumn('donation_type_id', 'donation_type');
            $table->string('donor_name')->nullable()->change();
            $table->string('donation_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grants', function (Blueprint $table) {
            $table->renameColumn('donor_name', 'donor_id');
            $table->string('donor_id')->change();
            $table->string('donation_type_id')->change();
        });
    }
};
