<?php

namespace App\Events;

use App\Models\AbstractWorkflow;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class StateChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public AbstractWorkflow $workflow;
    public string $previousState;
    public string $currentState;

    /**
     * Create a new event instance.
     */
    public function __construct(AbstractWorkflow $workflow, string $previousState, string $currentState)
    {
        $this->workflow = $workflow;
        $this->previousState = $previousState;
        $this->currentState = $currentState;
    }
}
