<?php

namespace App\Listeners;

use App\Events\ModelChangedEvent;
use App\Models\Option;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Class ApiNotificationListener
 *
 * This listener handles the ModelChangedEvent and sends a notification to an external API
 * whenever a model is created, updated, or deleted.
 */
class ApiNotificationListener
{
    /**
     * Create a new listener instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param ModelChangedEvent $event
     * @return void
     */
    public function handle(ModelChangedEvent $event): void
    {
        // Get the model class name without namespace
        $modelName = class_basename($event->model);
        $modelId = $event->model->id;
        $operation = $event->operation;

        // Log the event for debugging
        Log::info("Model changed: {$modelName} with ID {$modelId} was {$operation}");

        // Get API URL from options
        $apiUrl = Option::where('option_name', 'notification_api_url')->value('option_value');

        if (empty($apiUrl)) {
            Log::warning('API notification URL is not configured in options.');
            return;
        }

        try {
            // Prepare payload for the API call
            $payload = [
                'model' => $modelName,
                'operation' => $operation,
                'id' => $modelId
            ];

            // Make the POST request to the API
            $response = Http::post($apiUrl, $payload);

            // Log the response
            if ($response->successful()) {
                Log::info("API notification sent successfully for {$modelName} with ID {$modelId}", [
                    'status_code' => $response->status(),
                    'response' => $response->json()
                ]);
            } else {
                Log::error("Failed to send API notification for {$modelName} with ID {$modelId}", [
                    'status_code' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Exception occurred while sending API notification for {$modelName} with ID {$modelId}", [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}