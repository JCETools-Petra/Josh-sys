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
        // Backup existing reservations data if table exists
        if (Schema::hasTable('reservations')) {
            // Get old data
            $oldReservations = DB::table('reservations')->get();

            // Drop old table
            Schema::dropIfExists('reservations');

            // Create new table with updated structure
            Schema::create('reservations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained()->cascadeOnDelete();
                $table->foreignId('guest_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('room_type_id')->nullable()->constrained()->cascadeOnDelete();
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

            // Migrate old data to new structure
            foreach ($oldReservations as $old) {
                // Find or create guest
                $guestId = null;
                if (!empty($old->guest_email)) {
                    $guest = DB::table('guests')->where('email', $old->guest_email)->first();
                    if (!$guest && !empty($old->guest_name)) {
                        $guestId = DB::table('guests')->insertGetId([
                            'property_id' => $old->property_id,
                            'name' => $old->guest_name,
                            'email' => $old->guest_email,
                            'phone' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        $guestId = $guest->id ?? null;
                    }
                }

                // Calculate nights
                $checkIn = new DateTime($old->checkin_date);
                $checkOut = new DateTime($old->checkout_date);
                $nights = $checkIn->diff($checkOut)->days;

                // Insert with new structure
                DB::table('reservations')->insert([
                    'property_id' => $old->property_id,
                    'guest_id' => $guestId,
                    'room_type_id' => $old->room_type_id,
                    'reservation_number' => 'RSV-' . str_pad($old->id, 6, '0', STR_PAD_LEFT),
                    'check_in_date' => $old->checkin_date,
                    'check_out_date' => $old->checkout_date,
                    'nights' => $nights,
                    'adults' => 1,
                    'children' => 0,
                    'room_rate_per_night' => $old->final_price ?? 0,
                    'total_room_charge' => ($old->final_price ?? 0) * $nights,
                    'deposit_amount' => 0,
                    'deposit_paid' => 0,
                    'source' => $old->source ?? 'phone',
                    'ota_name' => null,
                    'ota_booking_id' => null,
                    'status' => 'confirmed',
                    'status_changed_at' => null,
                    'special_requests' => null,
                    'notes' => null,
                    'cancellation_reason' => null,
                    'created_by' => $old->user_id,
                    'confirmed_by' => null,
                    'cancelled_by' => null,
                    'created_at' => $old->created_at,
                    'updated_at' => $old->updated_at,
                    'deleted_at' => null,
                ]);
            }

            return;
        }

        // If table doesn't exist, create new one
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
