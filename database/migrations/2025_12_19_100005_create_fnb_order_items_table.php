<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fnb_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fnb_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('fnb_menu_item_id')->constrained()->onDelete('cascade');

            // Quantity
            $table->integer('quantity')->default(1);

            // Pricing (snapshot at time of order)
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 12, 2)->virtualAs('quantity * unit_price');

            // Customization
            $table->text('special_instructions')->nullable()->comment('No ice, extra spicy, etc');

            // Status
            $table->enum('status', [
                'pending',
                'preparing',
                'ready',
                'served'
            ])->default('pending');
            $table->timestamp('status_changed_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('fnb_order_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fnb_order_items');
    }
};
