<?php

namespace Modules\EmployeeRecruitment\App\Console\Commands;

use App\Models\Option;
use App\Models\User;
use App\Notifications\StateOverdueNotification;
use App\Services\WorkflowService;
use Illuminate\Console\Command;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

// Checks if the suspended workflows have reached the given deadline and reject them
class CheckStateDeadlines extends Command
{
    protected $signature = 'employeerecruitment:check-state-deadline';
    protected $description = 'Check recruitment workflow state deadlines';

    public function handle()
    {
        $this->info('Felvételi folyamat státusz határidők ellenőrzése...');

        // Get all deadline options
        $options = Option::where('option_name', 'like', 'recruitment_process_%_deadline')
            ->pluck('option_value', 'option_name')
            ->toArray();

        $recruitmentWorkflows = RecruitmentWorkflow::where('deleted', false)
            ->whereNotIn('state', ['new_request', 'completed', 'suspended', 'rejected'])
            ->get()
            ->filter(function ($workflow) use ($options) {
                $optionName = 'recruitment_process_' . $workflow->state . '_deadline';
                $deadlineValue = $options[$optionName] ?? null;
                $deadline = ($deadlineValue !== null && $deadlineValue !== '') ? $deadlineValue : null;
            
                // check if the workflow was updated later than the given deadline
                return $deadline && $workflow->updated_at->lt(now()->subHours($deadline));
            });

        if ($recruitmentWorkflows->isEmpty()) {
            $this->info('Nincsen, adott státuszban lejárt határidejű felvételi kérelem');
            return;
        }

        $service = new WorkflowService();
        foreach ($recruitmentWorkflows as $recruitmentWorkflow)
        {
            $usersToApprove = $service->getResponsibleUsers($recruitmentWorkflow, true);
            foreach ($usersToApprove as $user) {
                $user = User::find($user['id']);
                $user->notify(new StateOverdueNotification(
                    $recruitmentWorkflow,
                    $options['recruitment_process_' . $recruitmentWorkflow->state . '_deadline'],
                    [$user->getSupervisor()->email]
                ));
                $this->info($user->name . ' értesítve \'' . $recruitmentWorkflow->name . '\' felvételi kérelmének lejárt státusz határidejéről');
            }
        }
    }
}
