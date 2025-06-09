<?php

namespace Modules\EmployeeRecruitment\App\Listeners;

use App\Events\RejectedEvent;
use App\Models\User;
use Modules\EmployeeRecruitment\App\Notifications\RejectedNotification;

/**
 * RejectedListener is an event listener that handles the RejectedEvent.
 * It retrieves the users involved in the workflow's history and sends them
 * a notification when the workflow is rejected.
 */
class RejectedListener
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
     * This method is triggered when the RejectedEvent is fired.
     * It retrieves the users involved in the workflow's history and sends them
     * a notification when the workflow is rejected.
     *
     * @param RejectedEvent $event
     * @return void
     */
    public function handle(RejectedEvent $event): void
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
        $event->workflow->createdBy->notify(new RejectedNotification($event->workflow, $emails));
    }
}
