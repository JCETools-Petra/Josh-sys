<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('financial_entries', function (Blueprint $table) {
            // Menambahkan kolom forecast setelah budget
            $table->decimal('forecast_value', 15, 2)->default(0)->after('budget_value');
        });
    }
    
    public function down()
    {
        Schema::table('financial_entries', function (Blueprint $table) {
            $table->dropColumn('forecast_value');
        });
    }
};
