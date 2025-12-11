<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class QueueUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public int $clinicId) {}

    public function broadcastOn()
    {
        return new Channel('clinic.' . $this->clinicId);
    }

    public function broadcastAs()
    {
        return 'QueueUpdated';
    }
}
