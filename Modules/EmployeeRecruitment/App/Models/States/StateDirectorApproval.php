<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\Room;
use App\Models\User;
use App\Models\Workgroup;
use Illuminate\Support\Facades\Auth;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

class StateDirectorApproval implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        if ($workflow instanceof RecruitmentWorkflow) {
            $workgroup_lead = 
                ($workflow->workgroup1 && $workflow->workgroup1->leader == $user->id) ||
                ($workflow->workgroup2 && $workflow->workgroup2->leader == $user->id);

            $room_numbers = explode(',', $workflow->entry_permissions);
            foreach ($room_numbers as $room_number) {
                $room = Room::where('room_number', $room_number)->first();

                if ($room) {
                    $workgroup = Workgroup::where('workgroup_number', $room->workgroup_number)->first();

                    if ($workgroup && $workgroup->leader == $user->id) {
                        $workgroup_lead = true;
                        break;
                    }
                }
            }

            $metaData = json_decode($workflow->meta_data, true);
            $already_approved_by_user = false;
            if (isset($metaData['group_lead_approval']['approval_user_ids']) && 
                in_array($user->id, $metaData['group_lead_approval']['approval_user_ids'])) {
                    $already_approved_by_user = true;
            }

            return $workgroup_lead && !$already_approved_by_user;
        } else {
            return false;
        }
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        if ($workflow instanceof RecruitmentWorkflow) {
            $metaData = json_decode($workflow->meta_data, true);

            $approval_user_ids = $metaData['group_lead_approval']['approval_user_ids'] ?? [];
            $approval_user_ids[] = Auth::id();

            $metaData['group_lead_approval']['approval_user_ids'] = $approval_user_ids;
            $workflow->meta_data = json_encode($metaData);

            $workgroup_lead = [
                optional($workflow->workgroup1)->leader,
                optional($workflow->workgroup2)->leader
            ];

            $room_numbers = explode(',', $workflow->entry_permissions);
            foreach ($room_numbers as $room_number) {
                $room = Room::where('room_number', $room_number)->first();

                if ($room) {
                    $workgroup = Workgroup::where('workgroup_number', $room->workgroup_number)->first();

                    if ($workgroup) {
                        $workgroup_lead[] = $workgroup->leader;
                    }
                }
            }
            $workgroup_lead = array_filter($workgroup_lead);

            $workflow->updated_by = Auth::id();
            $workflow->save();

            return count(array_diff($workgroup_lead, $approval_user_ids)) === 0;
        } else {
            return false;
        }
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_hr_lead_approval';
    }
}