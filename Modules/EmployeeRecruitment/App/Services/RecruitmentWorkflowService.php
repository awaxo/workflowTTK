<?php

namespace Modules\EmployeeRecruitment\App\Services;

use App\Models\User;
use App\Models\Workgroup;
use App\Models\CostCenter;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

class RecruitmentWorkflowService
{
    /**
     * Check if user has any permission that overrides the IT Head's limited access
     *
     * @param RecruitmentWorkflow $recruitment
     * @param User $user
     * @return bool
     */
    public function hasNonITHeadPermission(RecruitmentWorkflow $recruitment, User $user)
    {
        $userId = $user->id;

        try {
            // Check if user is a leader or laborAdministrator of workgroup1 or workgroup2
            if (
                ($recruitment->workgroup1_id && $this->isLeaderOrLabAdmin($recruitment->workgroup1_id, $userId)) ||
                ($recruitment->workgroup2_id && $this->isLeaderOrLabAdmin($recruitment->workgroup2_id, $userId))
            ) {
                return true;
            }

            // Check if user is a leadUser of any cost center related to the recruitment
            $costCenterFields = [
                'base_salary_cc1_id',
                'base_salary_cc2_id',
                'base_salary_cc3_id',
                'health_allowance_cc_id',
                'management_allowance_cc_id',
                'extra_pay_1_cc_id',
                'extra_pay_2_cc_id'
            ];

            foreach ($costCenterFields as $field) {
                if (!empty($recruitment->$field)) {
                    $costCenter = CostCenter::find($recruitment->$field);
                    if ($costCenter && $costCenter->lead_user_id === $userId) {
                        return true;
                    }
                }
            }

            // Check if user is a leader of specific workgroups
            $specificWorkgroupNumbers = [900, 901, 903, 908, 910, 911, 912, 914];
            $userWorkgroups = Workgroup::whereIn('workgroup_number', $specificWorkgroupNumbers)
                ->where('leader_id', $userId)
                ->exists();

            if ($userWorkgroups) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Error in hasNonITHeadPermission: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user is leader or laborAdministrator of a workgroup
     *
     * @param int $workgroupId
     * @param int $userId
     * @return bool
     */
    private function isLeaderOrLabAdmin($workgroupId, $userId)
    {
        $workgroup = Workgroup::find($workgroupId);
        
        if (!$workgroup) {
            return false;
        }
        
        return $workgroup->leader_id === $userId || 
               $workgroup->labor_administrator_id === $userId;
    }
}