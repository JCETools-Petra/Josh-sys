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
        Schema::table('hotel_rooms', function (Blueprint $table) {
            $table->text('assignment_notes')->nullable()->after('notes');
            $table->text('cleaning_notes')->nullable()->after('assignment_notes');
            $table->timestamp('assigned_at')->nullable()->after('assigned_hk_user_id');
            $table->unsignedBigInteger('assigned_by')->nullable()->after('assigned_at');

            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotel_rooms', function (Blueprint $table) {
            $table->dropForeign(['assigned_by']);
            $table->dropColumn(['assignment_notes', 'cleaning_notes', 'assigned_at', 'assigned_by']);
        });
    }
};
