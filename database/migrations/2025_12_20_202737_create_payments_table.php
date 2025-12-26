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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('payment_number')->unique();

            // Polymorphic relation - can be for room_stays or fnb_orders
            $table->morphs('payable');

            $table->enum('payment_method', ['cash', 'credit_card', 'debit_card', 'bank_transfer', 'other']);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('IDR');

            // For card payments
            $table->string('card_number_last4')->nullable();
            $table->string('card_holder_name')->nullable();
            $table->string('card_type')->nullable(); // Visa, Mastercard, etc.

            // For bank transfers
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('reference_number')->nullable();

            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('completed');
            $table->timestamp('payment_date');
            $table->foreignId('processed_by')->nullable()->constrained('users');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
