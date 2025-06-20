<?php

namespace Modules\EmployeeRecruitment\App\Console\Commands;

use App\Models\Option;
use App\Models\User;
use App\Services\WorkflowService;
use Illuminate\Console\Command;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

/**
 * CheckSuspendedDeadline is a console command that checks for recruitment workflows
 * that are in a suspended state and have exceeded their suspension threshold.
 * It resets the approvals and sends the workflows back for review.
 */
class CheckSuspendedDeadline extends Command
{
    protected $signature = 'employeerecruitment:check-suspended-deadline';
    protected $description = 'Check suspended workflow deadlines';

    /**
     * Execute the console command.
     *
     * This method retrieves all recruitment workflows that are in a suspended state,
     * checks if they have exceeded their suspension threshold, and resets their approvals
     * to send them back for review.
     */
    public function handle()
    {
        $this->info('Lejárt határidejű, felfüggesztett felvételi kérelmek ellenőrzése...');

        $suspendOption = Option::where('option_name', 'recruitment_auto_suspend_threshold')->first();
        $suspendThreshold = intval($suspendOption->option_value);
        
        $recruitmentWorkflows = RecruitmentWorkflow::where('state', 'suspended')
            ->where('deleted', false)
            ->where('updated_at', '<', now()->subHours($suspendThreshold))
            ->get();

        if ($recruitmentWorkflows->isEmpty()) {
            $this->info('Nincsen lejárt határidejű, felfüggesztett felvételi kérelem');
            return;
        }

        $service = new WorkflowService();
        $systemUser = User::where('email', 'rendszerfiok')->first();

        foreach ($recruitmentWorkflows as $recruitmentWorkflow) {
            $service->resetApprovals($recruitmentWorkflow);
            $service->storeMetadata($recruitmentWorkflow, 'Felvételi kérelem automatikusan elutasítva ' . $suspendThreshold . ' óra felfüggesztés után', 'rejections', $systemUser->id);
            $recruitmentWorkflow->workflow_apply('to_request_review');
            $recruitmentWorkflow->updated_by = User::where('email', 'rendszerfiok')->first()->id;
            $recruitmentWorkflow->save();
            $this->info($recruitmentWorkflow->name . ' felvételi kérelme visszaküldve újraellenőrzésre ' . $suspendThreshold . ' óra felfüggesztés után');
        }
    }
}
