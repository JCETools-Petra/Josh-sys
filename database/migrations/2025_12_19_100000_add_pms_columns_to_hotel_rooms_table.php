<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotel_rooms', function (Blueprint $table) {
            // Room Status Management
            $table->enum('status', [
                'vacant_clean',      // Kamar kosong & bersih (Ready to Sell)
                'vacant_dirty',      // Kamar kosong tapi kotor (perlu dibersihkan)
                'occupied',          // Kamar terisi tamu
                'maintenance',       // Kamar dalam perbaikan
                'out_of_order',      // Kamar rusak/tidak bisa dipakai
                'blocked'            // Kamar di-block (tidak dijual)
            ])->default('vacant_clean')->after('notes');

            // Housekeeping tracking
            $table->timestamp('last_cleaned_at')->nullable()->after('status');
            $table->foreignId('assigned_hk_user_id')->nullable()->constrained('users')->onDelete('set null')->after('last_cleaned_at');

            // Floor information for better organization
            $table->string('floor')->nullable()->after('room_number');

            // Smoking preference
            $table->boolean('is_smoking')->default(false)->after('capacity');

            // Room features for better guest experience
            $table->json('features')->nullable()->comment('Extra features: wifi, tv, minibar, etc')->after('is_smoking');
        });
    }

    public function down(): void
    {
        Schema::table('hotel_rooms', function (Blueprint $table) {
            // Drop foreign key first before dropping column
            $table->dropForeign(['assigned_hk_user_id']);

            $table->dropColumn([
                'status',
                'last_cleaned_at',
                'assigned_hk_user_id',
                'floor',
                'is_smoking',
                'features'
            ]);
        });
    }
};
