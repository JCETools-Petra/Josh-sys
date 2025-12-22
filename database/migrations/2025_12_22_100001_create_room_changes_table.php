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
        Schema::create('room_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_stay_id')->constrained()->onDelete('cascade');
            $table->foreignId('old_room_id')->nullable()->constrained('hotel_rooms')->onDelete('set null');
            $table->foreignId('new_room_id')->nullable()->constrained('hotel_rooms')->onDelete('set null');
            $table->enum('change_type', ['room_change', 'extend_stay']);
            $table->date('old_check_out_date')->nullable();
            $table->date('new_check_out_date')->nullable();
            $table->decimal('old_rate', 10, 2)->nullable();
            $table->decimal('new_rate', 10, 2)->nullable();
            $table->decimal('additional_charge', 10, 2)->default(0);
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('processed_at')->useCurrent();
            $table->timestamps();

            $table->index('property_id');
            $table->index('room_stay_id');
            $table->index('change_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_changes');
    }
};
