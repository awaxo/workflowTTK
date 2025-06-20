<?php

namespace Modules\EmployeeRecruitment\App\Listeners;

use App\Events\StateChangedEvent;

/**
 * DirectorApprovedListener is an event listener that handles the StateChangedEvent.
 * It checks if the previous state was 'director_approval' and performs actions accordingly.
 */
class DirectorApprovedListener
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
     * This method is triggered when the StateChangedEvent is fired.
     * It checks if the previous state was 'director_approval' and performs actions accordingly.
     *
     * @param StateChangedEvent $event
     * @return void
     */
    public function handle(StateChangedEvent $event): void
    {
        if ($event->previousState == 'director_approval') {
            //
        }
    }
}
