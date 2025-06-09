<?php

namespace App\Events;

use App\Models\AbstractWorkflow;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event triggered when an approver is assigned to a workflow.
 */
class ApproverAssignedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public AbstractWorkflow $workflow;

    /**
     * Create a new event instance.
     *
     * @param AbstractWorkflow $workflow
     */
    public function __construct(AbstractWorkflow $workflow)
    {
        $this->workflow = $workflow;
    }
}
