<?php

namespace Modules\EmployeeRecruitment\App\Console\Commands;

use App\Models\Option;
use Illuminate\Console\Command;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

class CheckSuspendedDeadline extends Command
{
    protected $signature = 'employeerecruitment:check-suspended-deadline';
    protected $description = 'Check suspended workflow deadlines';

    public function handle()
    {
        $this->info('Automatikusan törlendő felvételi kérelmek ellenőrzése...');

        $suspendThreshold = Option::where('option_name', 'recruitment_auto_suspend_threshold')->first()->option_value;
        
        $recruitmentWorkflows = RecruitmentWorkflow::where('state', 'suspended')
            ->where('deleted', false)
            ->where('updated_at', '<', now()->subHours($suspendThreshold))
            ->get();

        if ($recruitmentWorkflows->isEmpty()) {
            $this->info('Nincsen törlendő felvételi kérelem');
            return;
        }

        foreach ($recruitmentWorkflows as $recruitmentWorkflow) {
            $recruitmentWorkflow->deleted = 1;
            $recruitmentWorkflow->save();
            $this->info($recruitmentWorkflow->name . ' felvételi kérelme törölve ' . $suspendThreshold . ' óra felfüggesztés után');
        }
    }
}
