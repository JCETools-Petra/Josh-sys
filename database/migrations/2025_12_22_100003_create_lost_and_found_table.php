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
        Schema::create('lost_and_found', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->string('item_number')->unique(); // Auto-generated: LF-XXXXXX
            $table->string('item_name');
            $table->string('category'); // electronics, clothing, documents, jewelry, accessories, others
            $table->text('description');
            $table->string('color')->nullable();
            $table->string('brand')->nullable();

            // Location found
            $table->foreignId('hotel_room_id')->nullable()->constrained()->onDelete('set null');
            $table->string('location_found')->nullable(); // If not room: lobby, restaurant, etc.
            $table->date('date_found');
            $table->foreignId('found_by')->constrained('users')->onDelete('cascade');

            // Guest information
            $table->foreignId('guest_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('room_stay_id')->nullable()->constrained()->onDelete('set null');

            // Status
            $table->enum('status', ['stored', 'claimed', 'disposed', 'donated'])->default('stored');
            $table->string('storage_location')->nullable(); // Where item is stored
            $table->date('disposal_date')->nullable(); // After 90 days unclaimed

            // Claim information
            $table->timestamp('claimed_at')->nullable();
            $table->foreignId('claimed_by_guest')->nullable()->constrained('guests')->onDelete('set null');
            $table->string('claimed_by_name')->nullable(); // If not registered guest
            $table->string('claimed_by_phone')->nullable();
            $table->text('claim_notes')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->onDelete('set null');

            // Photos
            $table->json('photos')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('property_id');
            $table->index('status');
            $table->index('date_found');
            $table->index('item_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lost_and_found');
    }
};
