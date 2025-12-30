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
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_drawer_id')->constrained('cash_drawers')->onDelete('cascade');
            $table->enum('type', ['in', 'out']); // in = cash masuk, out = cash keluar
            $table->enum('category', [
                'opening_balance',          // Saldo awal
                'deposit_payment',          // Pembayaran deposit (cash in)
                'deposit_refund',           // Pengembalian deposit (cash out)
                'room_payment',             // Pembayaran room (cash in)
                'change_given',             // Kembalian (cash out)
                'additional_charge',        // Pembayaran additional charge (cash in)
                'refund',                   // Refund (cash out)
                'top_up',                   // Top up dari kasir (cash in)
                'deposit_to_cashier',       // Setor ke kasir (cash out)
                'adjustment',               // Penyesuaian (in/out)
                'other'                     // Lainnya
            ]);
            $table->decimal('amount', 15, 2);
            $table->string('reference_type')->nullable(); // RoomStay, Folio, etc
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Indexes
            $table->index(['cash_drawer_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
