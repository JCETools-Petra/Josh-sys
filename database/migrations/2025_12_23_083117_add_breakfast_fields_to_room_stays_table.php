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
        Schema::table('room_stays', function (Blueprint $table) {
            $table->boolean('with_breakfast')->default(false)->after('children');
            $table->decimal('breakfast_rate', 10, 2)->nullable()->after('with_breakfast');
            $table->decimal('total_breakfast_charge', 12, 2)->default(0)->after('breakfast_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_stays', function (Blueprint $table) {
            $table->dropColumn(['with_breakfast', 'breakfast_rate', 'total_breakfast_charge']);
        });
    }
};
