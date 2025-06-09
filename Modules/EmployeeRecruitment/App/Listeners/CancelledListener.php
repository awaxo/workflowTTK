<?php

namespace Modules\EmployeeRecruitment\App\Listeners;

use App\Events\CancelledEvent;
use App\Models\User;
use Modules\EmployeeRecruitment\App\Notifications\CancelledNotification;

/**
 * CancelledListener is an event listener that handles the CancelledEvent.
 * It retrieves the users involved in the workflow's history and sends them
 * a notification when the workflow is cancelled.
 */
class CancelledListener
{
    /**
     * Create a new listener instance.
     *
     * This constructor does not require any dependencies, but it can be extended in the future
     * if needed for dependency injection.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * This method is triggered when the CancelledEvent is fired.
     * It retrieves the users involved in the workflow's history and sends them
     * a notification when the workflow is cancelled.
     *
     * @param CancelledEvent $event
     * @return void
     */
    public function handle(CancelledEvent $event): void
    {
        $metaData = json_decode($event->workflow->meta_data, true);
        $history = $metaData['history'] ?? [];
        $userIds = array_unique(array_column($history, 'user_id'));

        $emails = [];
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $emails[] = $user->email;
            }
        }
        $emails = array_unique($emails);
        $event->workflow->createdBy->notify(new CancelledNotification($event->workflow, $emails));
    }
}
