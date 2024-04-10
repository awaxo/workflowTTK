<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

/**
 * The state of the recruitment process when the IT head has to approve the recruitment.
 */
class StateProjectCoordinationLeadApproval implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        $workgroup911 = Workgroup::where('workgroup_number', 911)->first();
        return $workgroup911 && $workgroup911->leader === $user->id;
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        if ($workflow instanceof RecruitmentWorkflow) {
            $metaData = json_decode($workflow->meta_data, true);
            $postFinancedExists = false;

            if (isset($metaData['additional_fields']) && is_array($metaData['additional_fields'])) {
                foreach ($metaData['additional_fields'] as $field) {
                    if (isset($field['post_financed_application']) && $field['post_financed_application'] === true) {
                        $postFinancedExists = true;
                        break;
                    }
                }
            }

            if ($postFinancedExists) {
                return 'post_financing_approval';
            } else {
                return 'registration';
            }
        }
    }
}