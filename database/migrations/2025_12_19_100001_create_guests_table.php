<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('title')->nullable()->comment('Mr, Mrs, Ms, Dr, etc');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('full_name')->virtualAs('CONCAT(first_name, " ", COALESCE(last_name, ""))');

            // Contact Information
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('phone_country_code')->default('62');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Indonesia');
            $table->string('postal_code')->nullable();

            // Identification
            $table->enum('id_type', ['ktp', 'passport', 'sim', 'other'])->default('ktp');
            $table->string('id_number')->unique();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('nationality')->default('Indonesian');

            // Guest Type & Preferences
            $table->enum('guest_type', ['individual', 'corporate', 'vip', 'group'])->default('individual');
            $table->string('company_name')->nullable();
            $table->text('special_requests')->nullable();
            $table->text('preferences')->nullable()->comment('Room preferences, dietary restrictions, etc');

            // Marketing & Analytics
            $table->string('source')->nullable()->comment('How they found us: walk-in, OTA, referral, etc');
            $table->boolean('is_blacklisted')->default(false);
            $table->text('blacklist_reason')->nullable();

            // Statistics (akan di-update via triggers/observers)
            $table->integer('total_stays')->default(0);
            $table->decimal('lifetime_value', 15, 2)->default(0);
            $table->timestamp('last_stay_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index('email');
            $table->index('phone');
            $table->index('guest_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
