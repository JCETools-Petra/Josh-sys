<?php

namespace App\Listeners;

use App\Events\RoomStatusChanged;
use App\Models\HkTask;
use Illuminate\Support\Facades\Log;

class CreateHkTaskForDirtyRoom
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

        // Check if task already exists for this room today
        $existingTask = HkTask::where('hotel_room_id', $room->id)
            ->where('task_type', 'daily_cleaning')
            ->whereDate('task_date', today())
            ->where('status', 'pending')
            ->exists();

        if ($existingTask) {
            Log::info('HkTask already exists for dirty room, skipping creation', [
                'room_id' => $room->id,
                'room_number' => $room->room_number,
            ]);
            return;
        }

        // Create automatic cleaning task
        try {
            $task = HkTask::create([
                'property_id' => $room->property_id,
                'hotel_room_id' => $room->id,
                'task_date' => today(),
                'task_type' => 'daily_cleaning',
                'priority' => 'high', // Guest checkout = high priority
                'status' => 'pending',
                'checklist' => HkTask::getDefaultChecklist('daily_cleaning'),
                'completed_items' => [],
                'notes' => "Auto-created after guest checkout from room {$room->room_number}",
            ]);

            Log::info('HkTask automatically created for dirty room', [
                'task_id' => $task->id,
                'room_id' => $room->id,
                'room_number' => $room->room_number,
                'property_id' => $room->property_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create HkTask for dirty room', [
                'room_id' => $room->id,
                'room_number' => $room->room_number,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
