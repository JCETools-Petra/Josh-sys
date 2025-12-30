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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id');
            $table->string('refund_number', 50)->unique();
            $table->unsignedBigInteger('room_stay_id');
            $table->unsignedBigInteger('original_payment_id')->nullable(); // Deposit payment being refunded
            $table->decimal('amount', 15, 2);
            $table->enum('refund_method', ['cash', 'bank_transfer', 'credit_card', 'debit_card', 'other'])->default('cash');
            $table->enum('status', ['pending', 'processed', 'cancelled'])->default('pending');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();

            // Bank transfer details
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('reference_number')->nullable();

            // Processing details
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->foreign('room_stay_id')->references('id')->on('room_stays')->onDelete('cascade');
            $table->foreign('original_payment_id')->references('id')->on('payments')->onDelete('set null');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('property_id');
            $table->index('room_stay_id');
            $table->index('status');
            $table->index('refund_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
