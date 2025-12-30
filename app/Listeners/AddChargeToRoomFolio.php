<?php

namespace App\Listeners;

use App\Events\FnbOrderCompleted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AddChargeToRoomFolio
{
    /**
     * Handle the event.
     */
    public function handle(FnbOrderCompleted $event): void
    {
        $order = $event->order;

        // Only process orders that are charged to room
        if ($order->payment_status !== 'charge_to_room' || !$order->room_stay_id) {
            return;
        }

        $roomStay = $order->roomStay;

        if (!$roomStay) {
            Log::warning('FnbOrder has room_stay_id but RoomStay not found', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'room_stay_id' => $order->room_stay_id,
            ]);
            return;
        }

        try {
            DB::transaction(function () use ($order, $roomStay) {
                // Get current F&B charges or initialize to 0
                $currentFnbCharges = $roomStay->fnb_charges ?? 0;

                // Add this order's total to F&B charges
                $newFnbCharges = $currentFnbCharges + $order->total_amount;

                // Update room stay with new F&B charges
                $roomStay->update([
                    'fnb_charges' => $newFnbCharges,
                ]);

                Log::info('F&B charge added to room folio', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'room_stay_id' => $roomStay->id,
                    'room_number' => $roomStay->hotelRoom->room_number ?? 'N/A',
                    'charge_amount' => $order->total_amount,
                    'previous_fnb_total' => $currentFnbCharges,
                    'new_fnb_total' => $newFnbCharges,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to add F&B charge to room folio', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
