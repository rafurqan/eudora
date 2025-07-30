<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('education_levels', function (Blueprint $table) {
            $table->string('code', 10)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('education_levels', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
