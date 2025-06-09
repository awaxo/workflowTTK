<?php

namespace App\Events;

use App\Models\Delegation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/*
 * Event triggered when a delegation is created.
 */
class DelegationCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Delegation $delegation;

    /**
     * Create a new event instance.
     *
     * @param Delegation $delegation
     */
    public function __construct(Delegation $delegation)
    {
        $this->delegation = $delegation;
    }
}