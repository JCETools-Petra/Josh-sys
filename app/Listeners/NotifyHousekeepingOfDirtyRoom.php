<?php

namespace App\Listeners;

use App\Events\RoomStatusChanged;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

class NotifyHousekeepingOfDirtyRoom
{
    /**
     * Handle the event.
     */
    public function handle(RoomStatusChanged $event): void
    {
        // Only act when room becomes dirty (vacant_dirty)
        if ($event->newStatus !== 'vacant_dirty') {
            return;
        }

        $room = $event->room;

        // Log activity for housekeeping visibility
        ActivityLog::create([
            'user_id' => $event->changedBy,
            'property_id' => $room->property_id,
            'action' => 'room_needs_cleaning',
            'description' => "Kamar {$room->room_number} memerlukan pembersihan (guest check-out)",
            'loggable_id' => $room->id,
            'loggable_type' => get_class($room),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        Log::info('Housekeeping notified of dirty room', [
            'room_id' => $room->id,
            'room_number' => $room->room_number,
            'property_id' => $room->property_id,
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,
        ]);

        // Future: Send WhatsApp/SMS notification to housekeeping staff
        // $this->sendWhatsAppNotification($room);
    }
}
