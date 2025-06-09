<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event triggered when a model is changed (created, updated, deleted).
 */
class ModelChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Model $model;
    public string $operation;

    /**
     * Create a new event instance.
     *
     * @param Model $model
     * @param string $operation 'created', 'updated', or 'deleted'
     */
    public function __construct(Model $model, string $operation)
    {
        $this->model = $model;
        $this->operation = $operation; // 'created', 'updated', 'deleted'
    }
}