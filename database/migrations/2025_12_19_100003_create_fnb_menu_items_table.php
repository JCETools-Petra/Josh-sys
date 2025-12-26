<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fnb_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');

            // Menu Information
            $table->string('name');
            $table->string('code')->nullable()->comment('Menu code: BF-001, LN-002, etc');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();

            // Category
            $table->enum('category', [
                'breakfast',
                'lunch',
                'dinner',
                'appetizer',
                'main_course',
                'dessert',
                'beverage',
                'snack',
                'alcohol'
            ]);

            // Subcategory for better organization
            $table->string('subcategory')->nullable()->comment('Indonesian, Western, Chinese, etc');

            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('cost', 10, 2)->nullable()->comment('Cost of goods sold');
            $table->decimal('tax_rate', 5, 2)->default(10.00)->comment('Tax percentage');
            $table->decimal('service_charge_rate', 5, 2)->default(5.00)->comment('Service charge percentage');

            // Availability
            $table->boolean('is_available')->default(true);
            $table->time('available_from')->nullable()->comment('Available time start');
            $table->time('available_until')->nullable()->comment('Available time end');

            // Dietary Information
            $table->boolean('is_vegetarian')->default(false);
            $table->boolean('is_halal')->default(true);
            $table->boolean('is_spicy')->default(false);
            $table->text('allergens')->nullable()->comment('List of allergens');

            // Preparation
            $table->integer('prep_time_minutes')->default(15)->comment('Preparation time in minutes');

            // Statistics
            $table->integer('total_sold')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['property_id', 'category']);
            $table->index('is_available');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fnb_menu_items');
    }
};
