<?php

namespace Modules\EmployeeRecruitment\App\Listeners;

use App\Events\RejectedEvent;
use App\Models\User;
use Modules\EmployeeRecruitment\App\Notifications\CancelledNotification;

class CancelledListener
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

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $user->notify(new CancelledNotification($event->workflow));
            }
        }
    }
}
