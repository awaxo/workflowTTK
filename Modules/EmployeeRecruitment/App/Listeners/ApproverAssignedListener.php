<?php

namespace Modules\EmployeeRecruitment\App\Listeners;

use App\Events\ApproverAssignedEvent;
use App\Models\User;
use Modules\EmployeeRecruitment\App\Notifications\ApproverAssignedNotification;

class ApproverAssignedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
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
