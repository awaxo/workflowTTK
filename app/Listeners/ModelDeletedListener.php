<?php

namespace App\Listeners;

use App\Events\ModelChangedEvent;
use App\Models\Institute;
use App\Models\User;
use App\Models\Workgroup;
use App\Services\CascadeDeleteService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener for processing model deletion events and triggering cascade operations
 */
class ModelDeletedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * The time (seconds) before the job should be retried.
     *
     * @var array
     */
    public $backoff = [10, 60, 300];

    /**
     * The cascade delete service
     *
     * @var CascadeDeleteService
     */
    protected $cascadeDeleteService;

    /**
     * Create the event listener.
     *
     * @param CascadeDeleteService $cascadeDeleteService
     * @return void
     */
    public function __construct(CascadeDeleteService $cascadeDeleteService)
    {
        $this->cascadeDeleteService = $cascadeDeleteService;
    }

    /**
     * Handle the event.
     *
     * @param ModelChangedEvent $event
     * @return void
     */
    public function handle(ModelChangedEvent $event)
    {
        // Only process deletion events
        if ($event->operation !== 'deleted') {
            return;
        }

        Log::info("ModelDeletedListener: Processing deletion event for " . get_class($event->model) . " with ID: " . $event->model->id);

        try {
            // Handle different model types
            switch (true) {
                case $event->model instanceof Workgroup:
                    Log::info("ModelDeletedListener: Processing workgroup deletion, ID: " . $event->model->id);
                    $this->cascadeDeleteService->handleWorkgroupDeletion($event->model);
                    break;

                case $event->model instanceof User:
                    Log::info("ModelDeletedListener: Processing user deletion, ID: " . $event->model->id);
                    $this->cascadeDeleteService->handleUserDeletion($event->model);
                    break;

                case $event->model instanceof Institute:
                    Log::info("ModelDeletedListener: Processing institute deletion, ID: " . $event->model->id);
                    $this->cascadeDeleteService->handleInstituteDeletion($event->model);
                    break;

                default:
                    Log::info("ModelDeletedListener: No cascade delete handler for " . get_class($event->model));
                    break;
            }
        } catch (\Exception $e) {
            Log::error("ModelDeletedListener: Error processing deletion event: " . $e->getMessage(), [
                'model_type' => get_class($event->model),
                'model_id' => $event->model->id,
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Release the job back to the queue with a delay
            $this->release($this->backoff[$this->attempts() - 1] ?? 600);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param ModelChangedEvent $event
     * @param \Throwable $exception
     * @return void
     */
    public function failed(ModelChangedEvent $event, \Throwable $exception)
    {
        Log::critical("ModelDeletedListener: Failed to process deletion event after multiple attempts", [
            'model_type' => get_class($event->model),
            'model_id' => $event->model->id,
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        // Additional failure handling could be added here
        // For example, notification to admins or adding to a failed jobs log
    }
}