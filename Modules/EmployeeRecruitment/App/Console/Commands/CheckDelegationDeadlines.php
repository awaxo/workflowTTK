<?php

namespace Modules\EmployeeRecruitment\App\Console\Commands;

use App\Models\Delegation;
use Illuminate\Console\Command;

/**
 * CheckDelegationDeadlines is a console command that checks for ended delegations
 * and marks them as deleted. It is intended to be run periodically to clean up
 * old delegations.
 */
class CheckDelegationDeadlines extends Command
{
    protected $signature = 'employeerecruitment:check-delegation-deadline';
    protected $description = 'Check delegation deadlines';

    /**
     * Execute the console command.
     *
     * This method retrieves all delegations that have ended and marks them as deleted.
     * It also logs the details of each delegation that is marked as deleted.
     */
    public function handle()
    {
        $this->info('Aktív helyettesítések ellenőrzése...');

        // Get all non deleted delegations which are ended
        $delegations = Delegation::where('deleted', 0)
            ->where('end_date', '<', now())
            ->with('delegateUser')
            ->with('originalUser')
            ->get();

        if ($delegations->isEmpty()) {
            $this->info('Jelenleg nincsen aktív, törlendő helyettesítés');
            return;
        }

        // set $delegations to deleted
        foreach ($delegations as $delegation) {
            $delegation->deleted = 1;
            $delegation->save();
            $this->info($delegation->originalUser->name . ' / ' . $delegation->delegateUser->name . ' helyettesítés törölve');
        }
    }
}
