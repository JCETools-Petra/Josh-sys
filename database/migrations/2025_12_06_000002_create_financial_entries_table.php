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
        Schema::create('financial_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('financial_category_id')->constrained()->onDelete('cascade');
            $table->year('year');
            $table->integer('month'); // 1-12
            $table->decimal('actual_value', 15, 2)->default(0);
            $table->decimal('budget_value', 15, 2)->default(0);
            $table->timestamps();

            // Unique constraint: one entry per property, category, year, and month
            $table->unique(['property_id', 'financial_category_id', 'year', 'month'], 'unique_financial_entry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_entries');
    }
};
