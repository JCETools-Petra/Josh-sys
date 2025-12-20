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
        Schema::table('daily_incomes', function (Blueprint $table) {
            // Add new detailed revenue columns
            $table->decimal('beverage_income', 15, 2)->nullable()->after('dinner_income');
            $table->decimal('package_income', 15, 2)->nullable()->after('beverage_income');
            $table->decimal('rental_area_income', 15, 2)->nullable()->after('package_income');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_incomes', function (Blueprint $table) {
            $table->dropColumn(['beverage_income', 'package_income', 'rental_area_income']);
        });
    }
};
