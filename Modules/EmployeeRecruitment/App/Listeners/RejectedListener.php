<?php

namespace Modules\EmployeeRecruitment\App\Listeners;

use App\Events\RejectedEvent;
use App\Models\User;
use Modules\EmployeeRecruitment\App\Notifications\RejectedNotification;

class RejectedListener
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
