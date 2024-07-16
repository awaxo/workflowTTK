<?php

namespace App\Events;

use App\Models\AbstractWorkflow;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StateChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public AbstractWorkflow $workflow;
    public string $previousState;
    public string $currentState;
    public string $message;

    /**
     * Create a new event instance.
     */
    public function __construct(AbstractWorkflow $workflow, string $previousState, string $currentState, string $message = '')
    {
        $this->workflow = $workflow;
        $this->previousState = $previousState;
        $this->currentState = $currentState;
        $this->message = $message;
    }
}
