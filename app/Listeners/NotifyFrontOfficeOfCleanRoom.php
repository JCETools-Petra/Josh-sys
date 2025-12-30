<?php

namespace App\Listeners;

use App\Events\RoomStatusChanged;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

class NotifyFrontOfficeOfCleanRoom
{
    /**
     * Handle the event.
     */
    public function handle(RoomStatusChanged $event): void
    {
        // Only act when room becomes clean (vacant_clean)
        if ($event->newStatus !== 'vacant_clean') {
            return;
        }

        $room = $event->room;

        // Log activity for front office visibility
        ActivityLog::create([
            'user_id' => $event->changedBy,
            'property_id' => $room->property_id,
            'action' => 'room_ready_for_sale',
            'description' => "Kamar {$room->room_number} siap untuk dijual (pembersihan selesai)",
            'loggable_id' => $room->id,
            'loggable_type' => get_class($room),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        Log::info('Front Office notified of clean room', [
            'room_id' => $room->id,
            'room_number' => $room->room_number,
            'property_id' => $room->property_id,
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,
        ]);

        // Future: Send notification to Front Office staff
        // $this->notifyFrontOfficeStaff($room);
    }
}
