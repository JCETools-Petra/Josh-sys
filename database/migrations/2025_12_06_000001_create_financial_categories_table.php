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
        Schema::create('financial_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('financial_categories')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->enum('type', ['revenue', 'expense', 'calculated'])->default('expense');
            $table->boolean('is_payroll')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Unique constraint: code must be unique per property (when not null)
            $table->unique(['property_id', 'code'], 'unique_code_per_property');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_categories');
    }
};
