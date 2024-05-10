<?php

namespace App\Events;

use App\Models\AbstractWorkflow;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApproverAssignedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public AbstractWorkflow $workflow;

    /**
     * Create a new event instance.
     */
    public function __construct(AbstractWorkflow $workflow)
    {
        $this->workflow = $workflow;
    }
}
