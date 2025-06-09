<?php

namespace App\Events;

use App\Models\AbstractWorkflow;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event triggered when the state of a workflow changes.
 */
class StateChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public AbstractWorkflow $workflow;
    public string $previousState;
    public string $currentState;
    public string $message;

    /**
     * Create a new event instance.
     *
     * @param AbstractWorkflow $workflow
     * @param string $previousState
     * @param string $currentState
     * @param string $message
     */
    public function __construct(AbstractWorkflow $workflow, string $previousState, string $currentState, string $message = '')
    {
        $this->workflow = $workflow;
        $this->previousState = $previousState;
        $this->currentState = $currentState;
        $this->message = $message;
    }
}
