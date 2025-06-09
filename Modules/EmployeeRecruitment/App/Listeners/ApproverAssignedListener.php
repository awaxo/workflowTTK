<?php

namespace Modules\EmployeeRecruitment\App\Listeners;

use App\Events\ApproverAssignedEvent;
use App\Models\User;
use Modules\EmployeeRecruitment\App\Notifications\ApproverAssignedNotification;

/*
 * ApproverAssignedListener is an event listener that handles the ApproverAssignedEvent.
 * It retrieves the responsible users for the workflow's current state and sends them
 * a notification if they have enabled email notifications for recruitment approval.
 */
class ApproverAssignedListener
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

    /*
     * Handle the event.
     *
     * This method is triggered when the ApproverAssignedEvent is fired.
     * It retrieves the responsible users for the workflow's current state and sends them
     * a notification if they have enabled email notifications for recruitment approval.
     *
     * @param ApproverAssignedEvent $event
     * @return void
     */
    public function handle(ApproverAssignedEvent $event): void
    {
        $stateClassShortName = 'State' . str_replace(' ', '', ucwords(str_replace('_', ' ', $event->workflow->state)));
        $stateClass = "Modules\\EmployeeRecruitment\\App\\Models\\States\\{$stateClassShortName}";

        if (class_exists($stateClass)) {
            $stateInstance = new $stateClass();
            $responsibleUsers = $stateInstance->getResponsibleUsers($event->workflow);

            // notify all users in responsibleUsers who has in notification_preferences email->recruitment->approval_notification = true
            foreach ($responsibleUsers as $receiver) {
                if (isset($receiver['notification_preferences']) && json_decode($receiver['notification_preferences'])->email->recruitment->approval_notification) {
                    $user = User::find($receiver['id']);
                    if ($user) {
                        $user->notify(new ApproverAssignedNotification($event->workflow));
                    }
                }
            }
        }
    }
}
