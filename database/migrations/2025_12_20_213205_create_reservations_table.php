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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->string('reservation_number')->unique();

            // Booking details
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('nights');
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);

            // Pricing
            $table->decimal('room_rate_per_night', 10, 2);
            $table->decimal('total_room_charge', 12, 2);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->decimal('deposit_paid', 10, 2)->default(0);

            // Source tracking
            $table->enum('source', ['walk_in', 'phone', 'email', 'website', 'ota', 'corporate', 'other'])->default('phone');
            $table->string('ota_name')->nullable();
            $table->string('ota_booking_id')->nullable();

            // Status
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'cancelled', 'no_show'])->default('pending');
            $table->timestamp('status_changed_at')->nullable();

            // Additional info
            $table->text('special_requests')->nullable();
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();

            // Staff tracking
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('confirmed_by')->nullable()->constrained('users');
            $table->foreignId('cancelled_by')->nullable()->constrained('users');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
