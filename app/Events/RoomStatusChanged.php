<?php

namespace App\Events;

use App\Models\HotelRoom;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public HotelRoom $room;
    public string $oldStatus;
    public string $newStatus;
    public ?int $changedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(HotelRoom $room, string $oldStatus, string $newStatus, ?int $changedBy = null)
    {
        $this->room = $room;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->changedBy = $changedBy ?? auth()->id();
    }
}
