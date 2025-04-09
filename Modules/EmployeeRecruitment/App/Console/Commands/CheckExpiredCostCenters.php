<?php

namespace Modules\EmployeeRecruitment\App\Console\Commands;

use App\Models\CostCenter;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\App\Notifications\ExpiredCostCentersNotification;

/**
 * This command checks for expired cost centers based on due_date
 * and automatically marks them as deleted, then notifies project coordinators.
 */
class CheckExpiredCostCenters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'costcenter:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for expired cost centers and marks them as deleted, then notifies project coordinators';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired cost centers...');

        // Get current date at the beginning of the day
        $today = Carbon::now()->startOfDay();

        // Find all active cost centers with due_date in the past
        $expiredCostCenters = CostCenter::where('deleted', 0)
            ->whereNotNull('due_date')
            ->where('due_date', '<', $today)
            ->get();

        if ($expiredCostCenters->isEmpty()) {
            $this->info('No expired cost centers found.');
            return;
        }

        $this->info('Found ' . $expiredCostCenters->count() . ' expired cost centers.');
        
        // Group cost centers by project coordinator
        $groupedByCoordinator = $expiredCostCenters->groupBy('project_coordinator_user_id');
        
        // Create a system user for audit purposes
        $systemUser = User::withFeatured()->where('featured', 1)->first();
        $systemUserId = $systemUser ? $systemUser->id : null;
        
        if (!$systemUser) {
            $this->error('System user not found. Cannot proceed with marking cost centers as deleted.');
            return;
        }

        // Process each group of cost centers
        foreach ($groupedByCoordinator as $coordinatorId => $costCenters) {
            $coordinator = User::find($coordinatorId);
            
            if (!$coordinator) {
                $this->error("Coordinator with ID {$coordinatorId} not found. Skipping notifications for related cost centers.");
                continue;
            }
            
            // Mark each cost center as deleted
            foreach ($costCenters as $costCenter) {
                $costCenter->deleted = 1;
                $costCenter->updated_by = $systemUserId;
                $costCenter->save();
                
                $this->info("Cost center '{$costCenter->cost_center_code}' ({$costCenter->name}) marked as deleted.");
                Log::info("Cost center '{$costCenter->cost_center_code}' automatically marked as deleted due to expiration date.");
            }
            
            // Send notification to the coordinator with their related cost centers
            $this->sendNotificationToCoordinator($coordinator, $costCenters);
        }
        
        $this->info('All expired cost centers have been processed.');
    }
    
    /**
     * Send notification to a project coordinator about their expired cost centers
     *
     * @param User $coordinator
     * @param Collection $costCenters
     * @return void
     */
    private function sendNotificationToCoordinator(User $coordinator, Collection $costCenters)
    {
        try {
            $coordinator->notify(new ExpiredCostCentersNotification($costCenters));
            $this->info("Notification sent to coordinator: {$coordinator->name} ({$coordinator->email})");
        } catch (\Exception $e) {
            $this->error("Failed to send notification to coordinator {$coordinator->name}: " . $e->getMessage());
            Log::error("Failed to send expired cost centers notification to {$coordinator->email}: " . $e->getMessage());
        }
    }
}