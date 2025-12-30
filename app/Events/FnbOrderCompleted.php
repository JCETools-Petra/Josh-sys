<?php

namespace App\Events;

use App\Models\FnbOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FnbOrderCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public FnbOrder $order;

    /**
     * Create a new event instance.
     */
    public function __construct(FnbOrder $order)
    {
        $this->order = $order;
    }
}
