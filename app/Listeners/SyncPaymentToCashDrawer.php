<?php

namespace App\Listeners;

use App\Events\PaymentCreated;
use App\Models\CashDrawer;
use App\Models\CashTransaction;
use App\Models\RoomStay;
use App\Models\FnbOrder;
use Illuminate\Support\Facades\Log;

class SyncPaymentToCashDrawer
{
    /**
     * Handle the event.
     */
    public function handle(PaymentCreated $event): void
    {
        $payment = $event->payment;

        // Only sync cash payments to cash drawer
        // Skip credit card, debit card, bank transfer, and room charge
        if (!in_array($payment->payment_method, ['cash', 'tunai'])) {
            return;
        }

        // Skip if payment doesn't have a property_id
        if (!$payment->property_id) {
            Log::warning('Payment has no property_id, cannot sync to cash drawer', [
                'payment_id' => $payment->id,
                'payment_number' => $payment->payment_number,
            ]);
            return;
        }

        // Find open cash drawer for this property
        $cashDrawer = CashDrawer::openForProperty($payment->property_id)->first();

        if (!$cashDrawer) {
            Log::warning('No open cash drawer found for property, cannot sync payment', [
                'payment_id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'property_id' => $payment->property_id,
            ]);
            return;
        }

        // Determine transaction category based on payable_type
        $category = $this->determineCategoryFromPayableType($payment->payable_type);

        // Create cash transaction
        try {
            CashTransaction::create([
                'cash_drawer_id' => $cashDrawer->id,
                'type' => 'in', // Payment is always cash IN
                'category' => $category,
                'amount' => $payment->amount,
                'reference_type' => Payment::class,
                'reference_id' => $payment->id,
                'description' => $this->buildDescription($payment),
                'created_by' => $payment->processed_by ?? auth()->id(),
            ]);

            Log::info('Payment synced to cash drawer successfully', [
                'payment_id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'cash_drawer_id' => $cashDrawer->id,
                'amount' => $payment->amount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to sync payment to cash drawer', [
                'payment_id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Determine transaction category based on payable type.
     */
    private function determineCategoryFromPayableType(?string $payableType): string
    {
        if (!$payableType) {
            return 'other';
        }

        // Map payable types to cash transaction categories
        $categoryMap = [
            RoomStay::class => 'room_payment',
            FnbOrder::class => 'additional_charge',
            'App\Models\Deposit' => 'deposit_payment',
        ];

        return $categoryMap[$payableType] ?? 'other';
    }

    /**
     * Build description for cash transaction.
     */
    private function buildDescription(Payment $payment): string
    {
        $parts = [];

        // Add payment number
        $parts[] = "Pembayaran {$payment->payment_number}";

        // Add payable information if available
        if ($payment->payable) {
            if ($payment->payable_type === RoomStay::class) {
                $roomStay = $payment->payable;
                $roomNumber = $roomStay->hotelRoom?->room_number ?? 'N/A';
                $parts[] = "- Kamar {$roomNumber}";
                if ($roomStay->guest) {
                    $parts[] = "- Tamu: {$roomStay->guest->full_name}";
                }
            } elseif ($payment->payable_type === FnbOrder::class) {
                $fnbOrder = $payment->payable;
                $parts[] = "- Pesanan F&B #{$fnbOrder->id}";
                if ($fnbOrder->roomStay?->hotelRoom) {
                    $roomNumber = $fnbOrder->roomStay->hotelRoom->room_number;
                    $parts[] = "- Kamar {$roomNumber}";
                }
            }
        }

        // Add payment method
        $parts[] = "- Metode: " . strtoupper($payment->payment_method);

        // Add notes if available
        if ($payment->notes) {
            $parts[] = "- Catatan: {$payment->notes}";
        }

        return implode(' ', $parts);
    }
}
