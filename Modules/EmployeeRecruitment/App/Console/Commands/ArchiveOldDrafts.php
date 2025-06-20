<?php

namespace Modules\EmployeeRecruitment\App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflowDraft;

/**
 * ArchiveOldDrafts is a console command that archives recruitment workflow drafts
 * created in the previous calendar year by marking them as deleted.
 * It is intended to be run periodically to clean up old drafts.
 */
class ArchiveOldDrafts extends Command
{
    protected $signature = 'employeerecruitment:archive-old-drafts';
    protected $description = 'Archive recruitment workflow drafts created in the previous calendar year';

    /**
     * Execute the console command.
     *
     * This method fetches all recruitment workflow drafts created in the previous year,
     * marks them as deleted, and saves the changes.
     * It also logs the user who performed the deletion.
     */
    public function handle()
    {
        $this->info('Archiving recruitment workflow drafts from previous year...');

        // Calculate last calendar year
        $previousYear = now()->year - 1;

        // Fetch all non-deleted drafts created during that year
        $drafts = RecruitmentWorkflowDraft::whereYear('created_at', $previousYear)
            ->where('deleted', false)
            ->get();

        if ($drafts->isEmpty()) {
            $this->info("No recruitment workflow drafts found from {$previousYear}.");
            return;
        }

        // (Optional) record who performed the deletion
        $systemUser = User::where('email', 'rendszerfiok')->first();

        foreach ($drafts as $draft) {
            $draft->deleted    = true;
            $draft->updated_by = $systemUser ? $systemUser->id : null;
            $draft->save();

            $this->info("Draft [ID: {$draft->id}] archived.");
        }

        $this->info('Done.');
    }
}
