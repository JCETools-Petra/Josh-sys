<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');

            // Polymorphic relation - bisa untuk room_stays atau fnb_orders
            $table->morphs('payable'); // Creates payable_id and payable_type

            // Transaction Information
            $table->string('transaction_number')->unique();
            $table->decimal('amount', 15, 2);

            // Payment Method
            $table->enum('payment_method', [
                'cash',
                'debit_card',
                'credit_card',
                'bank_transfer',
                'ewallet_gopay',
                'ewallet_ovo',
                'ewallet_dana',
                'qris',
                'other'
            ]);

            // Card Information (if applicable)
            $table->string('card_type')->nullable()->comment('Visa, Mastercard, etc');
            $table->string('card_last_four')->nullable();

            // Transfer Information (if applicable)
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('reference_number')->nullable();

            // Status
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->timestamp('completed_at')->nullable();

            // Staff Information
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');

            // Additional
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('transaction_number');
            $table->index(['property_id', 'status']);
            // Note: morphs() already creates index for payable_type and payable_id
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
