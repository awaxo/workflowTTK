<?php

namespace App\Events;

use App\Models\Delegation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DelegationAcceptedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Delegation $delegation;

    /**
     * Create a new event instance.
     */
    public function __construct(Delegation $delegation)
    {
        $this->delegation = $delegation;
    }
}