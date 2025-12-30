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
            $table->unsignedBigInteger('reservation_id')->nullable()->after('property_id');
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('set null');
            $table->index('reservation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_stays', function (Blueprint $table) {
            $table->dropForeign(['reservation_id']);
            $table->dropIndex(['reservation_id']);
            $table->dropColumn('reservation_id');
        });
    }
};
