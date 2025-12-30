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
        Schema::table('room_stays', function (Blueprint $table) {
            // Add fnb_charges field to track F&B charges added to room
            $table->decimal('fnb_charges', 15, 2)->default(0)->after('service_charge');

            // Add folio_locked_at timestamp to prevent concurrent modifications during checkout
            $table->timestamp('folio_locked_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_stays', function (Blueprint $table) {
            $table->dropColumn(['fnb_charges', 'folio_locked_at']);
        });
    }
};
