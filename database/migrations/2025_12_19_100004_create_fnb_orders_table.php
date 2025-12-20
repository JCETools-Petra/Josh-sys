<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fnb_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');

            // Order Type
            $table->enum('order_type', [
                'dine_in',           // Makan di tempat
                'room_service',      // Pesan ke kamar
                'takeaway',          // Bungkus
                'delivery'           // Delivery
            ])->default('dine_in');

            // Guest/Room Information (nullable untuk dine-in tanpa tamu hotel)
            $table->foreignId('guest_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('room_stay_id')->nullable()->constrained()->onDelete('set null')->comment('Link to room stay if room service');
            $table->foreignId('hotel_room_id')->nullable()->constrained()->onDelete('set null')->comment('Room number if room service');

            // Dine-in specific
            $table->string('table_number')->nullable();
            $table->integer('number_of_guests')->nullable();

            // Customer Information (untuk non-hotel guests)
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();

            // Order Details
            $table->string('order_number')->unique();
            $table->dateTime('order_time');
            $table->dateTime('delivery_time')->nullable()->comment('When to serve/deliver');

            // Pricing
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('service_charge', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('discount_reason')->nullable();
            $table->decimal('delivery_charge', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->virtualAs('subtotal + tax_amount + service_charge + delivery_charge - discount_amount');

            // Payment
            $table->enum('payment_status', ['unpaid', 'paid', 'charge_to_room'])->default('unpaid');
            $table->enum('payment_method', ['cash', 'card', 'ewallet', 'transfer', 'room_charge'])->nullable();
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->timestamp('paid_at')->nullable();

            // Order Status
            $table->enum('status', [
                'pending',           // Baru masuk
                'confirmed',         // Dikonfirmasi
                'preparing',         // Sedang dimasak
                'ready',             // Siap diantar
                'delivered',         // Sudah diantar
                'completed',         // Selesai
                'cancelled'          // Dibatalkan
            ])->default('pending');
            $table->timestamp('status_changed_at')->nullable();

            // Staff Information
            $table->foreignId('taken_by')->nullable()->constrained('users')->onDelete('set null')->comment('Staff yang menerima order');
            $table->foreignId('served_by')->nullable()->constrained('users')->onDelete('set null')->comment('Staff yang mengantar');

            // Additional Information
            $table->text('special_instructions')->nullable();
            $table->text('notes')->nullable();

            // Rating (optional for guest feedback)
            $table->integer('rating')->nullable()->comment('1-5 rating');
            $table->text('feedback')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('order_number');
            $table->index(['property_id', 'order_type']);
            $table->index(['property_id', 'status']);
            $table->index('order_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fnb_orders');
    }
};
