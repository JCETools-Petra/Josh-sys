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
        Schema::table('guests', function (Blueprint $table) {
            // Add unique index to email (nullable unique)
            $table->unique('email', 'guests_email_unique');

            // Add unique index to phone
            $table->unique('phone', 'guests_phone_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropUnique('guests_email_unique');
            $table->dropUnique('guests_phone_unique');
        });
    }
};
