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
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->decimal('bar_1', 10, 2)->default(0)->after('starting_bar');
            $table->decimal('bar_2', 10, 2)->default(0)->after('bar_1');
            $table->decimal('bar_3', 10, 2)->default(0)->after('bar_2');
            $table->decimal('bar_4', 10, 2)->default(0)->after('bar_3');
            $table->decimal('bar_5', 10, 2)->default(0)->after('bar_4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->dropColumn(['bar_1', 'bar_2', 'bar_3', 'bar_4', 'bar_5']);
        });
    }
};
