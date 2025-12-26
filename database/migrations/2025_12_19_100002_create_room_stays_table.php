<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_stays', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('hotel_room_id')->constrained()->onDelete('cascade');
            $table->foreignId('guest_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_type_id')->constrained()->onDelete('cascade');

            // Booking Information
            $table->string('confirmation_number')->unique();
            $table->enum('source', [
                'walk_in',
                'ota',
                'ta',           // Travel Agent
                'corporate',
                'government',
                'compliment',
                'house_use',
                'affiliate',
                'online'        // Booking langsung dari website
            ])->default('walk_in');
            $table->string('ota_name')->nullable()->comment('Traveloka, Booking.com, Agoda, etc');
            $table->string('ota_booking_id')->nullable();

            // Stay Dates
            $table->dateTime('check_in_date');
            $table->dateTime('check_out_date');
            $table->dateTime('actual_check_in')->nullable();
            $table->dateTime('actual_check_out')->nullable();
            $table->integer('nights')->virtualAs('DATEDIFF(check_out_date, check_in_date)');

            // Pricing
            $table->decimal('room_rate_per_night', 12, 2);
            $table->integer('bar_level')->nullable()->comment('BAR level saat booking: 1-5');
            $table->decimal('total_room_charge', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('service_charge', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->string('discount_reason')->nullable();

            // Guest Details
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->text('special_requests')->nullable();

            // Status Management
            $table->enum('status', [
                'reserved',      // Sudah booking tapi belum check-in
                'checked_in',    // Sedang menginap
                'checked_out',   // Sudah check-out
                'no_show',       // Tidak datang
                'cancelled'      // Dibatalkan
            ])->default('reserved');
            $table->timestamp('status_changed_at')->nullable();

            // Payment Information
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid');
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->virtualAs('total_room_charge + tax_amount + service_charge - discount_amount - paid_amount');

            // Staff Information
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('checked_out_by')->nullable()->constrained('users')->onDelete('set null');

            // Additional Info
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('confirmation_number');
            $table->index(['property_id', 'status']);
            $table->index(['check_in_date', 'check_out_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_stays');
    }
};
