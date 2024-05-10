<?php

namespace Modules\EmployeeRecruitment\App\Providers;

use App\Events\ApproverAssignedEvent;
use Modules\EmployeeRecruitment\App\Listeners\ApproverAssignedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ApproverAssignedEvent::class => [
            ApproverAssignedListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
