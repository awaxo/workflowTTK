<?php

namespace Modules\EmployeeRecruitment\App\Providers;

use App\Events\ApproverAssignedEvent;
use App\Events\CancelledEvent;
use App\Events\RejectedEvent;
use App\Events\StateChangedEvent;
use App\Events\SuspendedEvent;
use App\Events\WorkflowStartedEvent;
use App\Listeners\StateChangedListener;
use Modules\EmployeeRecruitment\App\Listeners\ApproverAssignedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\EmployeeRecruitment\App\Listeners\CancelledListener;
use Modules\EmployeeRecruitment\App\Listeners\DirectorApprovedListener;
use Modules\EmployeeRecruitment\App\Listeners\HrLeadApprovedListener;
use Modules\EmployeeRecruitment\App\Listeners\ObligeeApprovedListener;
use Modules\EmployeeRecruitment\App\Listeners\RecruitmentStartedListener;
use Modules\EmployeeRecruitment\App\Listeners\RejectedListener;
use Modules\EmployeeRecruitment\App\Listeners\SuspendedListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        WorkflowStartedEvent::class => [
            RecruitmentStartedListener::class,
        ],
        ApproverAssignedEvent::class => [
            ApproverAssignedListener::class,
        ],
        RejectedEvent::class => [
            RejectedListener::class,
        ],
        SuspendedEvent::class => [
            SuspendedListener::class,
        ],
        CancelledEvent::class => [
            CancelledListener::class,
        ],
        StateChangedEvent::class => [
            DirectorApprovedListener::class,
            ObligeeApprovedListener::class,
            HrLeadApprovedListener::class,
            StateChangedListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
