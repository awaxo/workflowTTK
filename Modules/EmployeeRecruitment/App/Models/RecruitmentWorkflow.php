<?php

namespace Modules\EmployeeRecruitment\App\Models;

use App\Models\AbstractWorkflow;
use App\Models\Position;
use App\Models\Workgroup;
use App\Models\CostCenter;
use App\Models\Delegation;
use App\Models\Institute;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\EmployeeRecruitment\Database\Factories\RecruitmentWorkflowFactory;
use ZeroDaHero\LaravelWorkflow\Traits\WorkflowTrait;

class RecruitmentWorkflow extends AbstractWorkflow
{
    use WorkflowTrait;

    /**
     * Get base query for recruitment workflows with optional permission exclusions
     * 
     * @param array $excludePermissions Array of permission keys to exclude from the check or complex exclusion data
     * @return Builder
     */
    public static function baseQuery(array $excludePermissions = []): Builder
    {
        $user = User::find(Auth::id());
        $workgroup901 = Workgroup::where('workgroup_number', 901)->first();
        $workgroup903 = Workgroup::where('workgroup_number', 903)->first();
        $workgroup910 = Workgroup::where('workgroup_number', 910)->first();
        $workgroup911 = Workgroup::where('workgroup_number', 911)->first();
        $workgroup915 = Workgroup::where('workgroup_number', 915)->first();

        $delegations = Delegation::where('delegate_user_id', $user->id)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();
        
        // *** Titkar or titkar delegate ***
        $allRoles = $user->roles->pluck('name')->toArray();

        // Add roles from each delegation's original user
        foreach ($delegations as $delegation) {
            $originalUserRoles = User::find($delegation->original_user_id)->roles->pluck('name')->toArray();
            $allRoles = array_merge($allRoles, $originalUserRoles);
        }
        
        // Filter for 'titkar_' roles and extract numbers, ensuring uniqueness
        $titkarRoleNumbers = array_unique(array_map(function($role) {
            return substr($role, 7);
        }, array_filter($allRoles, function($role) {
            return strpos($role, 'titkar_') === 0;
        })));
        // *** End titkar or titkar delegate ***

        $recruitmentsQuery = self::query();
        $orQueries = collect();

        // Define permission checks with keys that can be excluded
        $permissionChecks = [
            'administrator' => $user && $user->hasRole('adminisztrator'),
            'hr' => $user->workgroup->workgroup_number == 908,
            'obligee_approver' => (!in_array('obligee_approver', $excludePermissions) && 
                $workgroup901 && $workgroup901->leader_id === $user->id) ||
                (!in_array('obligee_approver', $excludePermissions) && 
                    $delegations->contains(function ($delegation) use ($workgroup901) {
                        return $delegation->type === 'obligee_approver' && $delegation->original_user_id === $workgroup901->leader_id;
                    })),
            'financial_countersign_approver' => (!in_array('financial_countersign_approver', $excludePermissions) && 
                $workgroup903 && $workgroup903->leader_id === $user->id) ||
                (!in_array('financial_countersign_approver', $excludePermissions) && 
                    $delegations->contains(function ($delegation) use ($workgroup903) {
                        return $delegation->type === 'financial_countersign_approver' && $delegation->original_user_id === $workgroup903->leader_id;
                    })),
            'workgroup_910_leader' => !in_array('workgroup_910_leader', $excludePermissions) && 
                $workgroup910 && $workgroup910->leader_id === $user->id,
            'project_coordination_lead' => (!in_array('project_coordination_lead', $excludePermissions) && 
                $workgroup911 && $workgroup911->leader_id === $user->id) ||
                (!in_array('project_coordination_lead', $excludePermissions) && 
                    $delegations->contains(function ($delegation) use ($workgroup911) {
                        return $delegation->type === 'project_coordination_lead' && $delegation->original_user_id === $workgroup911->leader_id;
                    })),
            'it_head' => (!in_array('it_head', $excludePermissions) && 
                $workgroup915 && $workgroup915->leader_id === $user->id) ||
                (!in_array('it_head', $excludePermissions) && 
                    $delegations->contains(function ($delegation) use ($workgroup915) {
                        return $delegation->type === 'it_head' && $delegation->original_user_id === $workgroup915->leader_id;
                    })),
            'secretary_9_fi' => (!in_array('secretary_9_fi', $excludePermissions) && 
                $user && $user->hasRole('titkar_9_fi')) ||
                (!in_array('secretary_9_fi', $excludePermissions) && 
                    $delegations->contains(function ($delegation) {
                        return $delegation->type === 'secretary_9_fi';
                    })),
            'secretary_9_gi' => (!in_array('secretary_9_gi', $excludePermissions) && 
                $user && $user->hasRole('titkar_9_gi')) ||
                (!in_array('secretary_9_gi', $excludePermissions) && 
                    $delegations->contains(function ($delegation) {
                        return $delegation->type === 'secretary_9_gi';
                    })),
            'registrator' => (!in_array('registrator', $excludePermissions) && 
                $user && $user->hasRole('munkaber_kotelezettsegvallalas_nyilvantarto')) ||
                (!in_array('registrator', $excludePermissions) && 
                    $delegations->contains(function ($delegation) {
                        return $delegation->type === 'registrator';
                    })),
        ];

        // If any of the permission checks pass and we're not excluding all permissions, return all workflows
        if (!in_array('all_special_permissions', $excludePermissions) && in_array(true, $permissionChecks, true)) {
            return self::query();
        }

        // Check if specific permission is not excluded AND the condition for that permission is met
        // For titkar roles
        if (!in_array('titkar_roles', $excludePermissions) && !empty($titkarRoleNumbers)) {
            foreach ($titkarRoleNumbers as $number) {
                $orQueries->push(function ($query) use ($number) {
                    $query->WhereHas('workgroup1', function ($query) use ($number) {
                        $query->where('workgroup_number', 'LIKE', "$number%");
                    })->orWhereHas('workgroup2', function ($query) use ($number) {
                        $query->where('workgroup_number', 'LIKE', "$number%");
                    });
                });
                $orQueries->push(function (Builder $query) use ($number) {
                    $query->whereHas('initiator_institute', function (Builder $q) use ($number) {
                        $q->where('group_level', $number);
                    });
                });
            }
        }

        // For directors of specific workgroups
        $allowedDirectorWorkgroups = ['100', '300', '400', '500', '600', '700', '800', '901', '903'];
        
        $hasDirectorRole = Workgroup::whereIn('workgroup_number', $allowedDirectorWorkgroups)
                ->where('leader_id', $user->id)
                ->exists() || 
            $delegations->contains(function ($delegation) use ($allowedDirectorWorkgroups) {
                if (strpos($delegation->type, 'director_') !== 0) {
                    return false;
                }
                
                // Extract the numeric part from the delegation type
                $number = filter_var($delegation->type, FILTER_SANITIZE_NUMBER_INT);
                return in_array($number, $allowedDirectorWorkgroups);
            });
                
        if (!in_array('director', $excludePermissions) && $hasDirectorRole) {
            
            $leaderWorkgroups = Workgroup::where('leader_id', $user->id)
                ->whereIn('workgroup_number', $allowedDirectorWorkgroups)
                ->get();

            $delegateWorkgroupNumbers = $delegations->filter(function ($delegation) use ($allowedDirectorWorkgroups) {
                if (strpos($delegation->type, 'director_') !== 0) {
                    return false;
                }
                // Extract the numeric part from the delegation type
                $number = filter_var($delegation->type, FILTER_SANITIZE_NUMBER_INT);
                return in_array($number, $allowedDirectorWorkgroups);
            })->map(function ($delegation) {
                // Extract the numeric part from the delegation type
                $number = filter_var($delegation->type, FILTER_SANITIZE_NUMBER_INT);
                return $number;
            })->unique();

            $delegateWorkgroups = collect();
            foreach ($delegateWorkgroupNumbers as $number) {
                $workgroup = Workgroup::where('workgroup_number', $number)->first();
                if ($workgroup) {
                    $delegateWorkgroups->push($workgroup);
                }
            }

            $allRelevantWorkgroups = $leaderWorkgroups->merge($delegateWorkgroups);

            $firstChars = $allRelevantWorkgroups->map(function ($workgroup) {
                return substr($workgroup->workgroup_number, 0, 1);
            })->unique()->toArray();

            $matchingWorkgroups = Workgroup::whereIn(DB::raw('LEFT(workgroup_number, 1)'), $firstChars)->get();
            $matchingWorkgroupIds = $matchingWorkgroups->pluck('id')->toArray();

            $orQueries->push(function ($query) use ($matchingWorkgroupIds) {
                $query->whereIn('workgroup_id_1', $matchingWorkgroupIds)
                    ->orWhereIn('workgroup_id_2', $matchingWorkgroupIds);
            });
        }

        // For group leaders with optional workgroup exclusion
        $excludedWorkgroupNumbers = $excludePermissions['excluded_workgroups'] ?? [];
        
        $hasGroupLeadRole = Workgroup::where('leader_id', $user->id)
            ->when(!empty($excludedWorkgroupNumbers), function($query) use ($excludedWorkgroupNumbers) {
                return $query->whereNotIn('workgroup_number', $excludedWorkgroupNumbers);
            })
            ->exists() || 
            $delegations->contains(function ($delegation) use ($excludedWorkgroupNumbers) {
                if (strpos($delegation->type, 'grouplead_') !== 0) {
                    return false;
                }
                // Extract the numeric part from the delegation type
                $number = filter_var($delegation->type, FILTER_SANITIZE_NUMBER_INT);
                return !in_array($number, $excludedWorkgroupNumbers);
            });
                
        if (!in_array('grouplead', $excludePermissions) && $hasGroupLeadRole) {
            
            $leaderWorkgroups = Workgroup::where('leader_id', $user->id)
                ->when(!empty($excludedWorkgroupNumbers), function($query) use ($excludedWorkgroupNumbers) {
                    return $query->whereNotIn('workgroup_number', $excludedWorkgroupNumbers);
                })
                ->get();

            $delegateWorkgroupNumbers = $delegations->filter(function ($delegation) use ($excludedWorkgroupNumbers) {
                if (strpos($delegation->type, 'grouplead_') !== 0) {
                    return false;
                }
                // Extract the numeric part from the delegation type
                $number = filter_var($delegation->type, FILTER_SANITIZE_NUMBER_INT);
                return !in_array($number, $excludedWorkgroupNumbers);
            })->map(function ($delegation) {
                // Extract the numeric part from the delegation type
                $number = filter_var($delegation->type, FILTER_SANITIZE_NUMBER_INT);
                return $number;
            })->unique();

            $delegateWorkgroups = collect();
            foreach ($delegateWorkgroupNumbers as $number) {
                $workgroup = Workgroup::where('workgroup_number', $number)->first();
                if ($workgroup) {
                    $delegateWorkgroups->push($workgroup);
                }
            }

            $allRelevantWorkgroups = $leaderWorkgroups->merge($delegateWorkgroups);
            $matchingWorkgroupIds = $allRelevantWorkgroups->pluck('id')->toArray();

            $orQueries->push(function ($query) use ($matchingWorkgroupIds) {
                $query->whereIn('workgroup_id_1', $matchingWorkgroupIds)
                    ->orWhereIn('workgroup_id_2', $matchingWorkgroupIds);
            });
        }

        // For cost center leads with optional cost center exclusion
        $excludedCostCenterIds = $excludePermissions['excluded_costcenter_lead_ids'] ?? [];
        
        $hasCostCenterLeadRole = CostCenter::where('lead_user_id', $user->id)
            ->when(!empty($excludedCostCenterIds), function($query) use ($excludedCostCenterIds) {
                return $query->whereNotIn('id', $excludedCostCenterIds);
            })
            ->exists() || 
            $delegations->contains(function ($delegation) use ($excludedCostCenterIds) {
                if (strpos($delegation->type, 'supervisor_workgroup_') !== 0) {
                    return false;
                }
                $originalUserId = $delegation->original_user_id;
                return CostCenter::where('lead_user_id', $originalUserId)
                    ->when(!empty($excludedCostCenterIds), function($query) use ($excludedCostCenterIds) {
                        return $query->whereNotIn('id', $excludedCostCenterIds);
                    })
                    ->exists();
            });
                
        if (!in_array('costcenter_lead', $excludePermissions) && $hasCostCenterLeadRole) {
            
            $leadCostCenters = CostCenter::where('lead_user_id', $user->id)
                ->when(!empty($excludedCostCenterIds), function($query) use ($excludedCostCenterIds) {
                    return $query->whereNotIn('id', $excludedCostCenterIds);
                })
                ->get();

            $delegateOriginalUserIds = $delegations->filter(function ($delegation) {
                return strpos($delegation->type, 'supervisor_workgroup_') === 0;
            })->map(function ($delegation) {
                return $delegation->original_user_id;
            })->unique();
            
            $delegateCostCenters = collect();
            foreach ($delegateOriginalUserIds as $originalUserId) {
                $costcenters = CostCenter::where('lead_user_id', $originalUserId)
                    ->when(!empty($excludedCostCenterIds), function($query) use ($excludedCostCenterIds) {
                        return $query->whereNotIn('id', $excludedCostCenterIds);
                    })
                    ->get();
                if ($costcenters->isNotEmpty()) {
                    $delegateCostCenters = $delegateCostCenters->merge($costcenters);
                }
            }

            $allRelevantCostCenters = $leadCostCenters->merge($delegateCostCenters);
            $matchingCostCenterIds = $allRelevantCostCenters->pluck('id')->toArray();

            $orQueries->push(function ($query) use ($matchingCostCenterIds) {
                $query->whereIn('base_salary_cost_center_1', $matchingCostCenterIds)
                    ->orWhereIn('base_salary_cost_center_2', $matchingCostCenterIds)
                    ->orWhereIn('base_salary_cost_center_3', $matchingCostCenterIds)
                    ->orWhereIn('health_allowance_cost_center_4', $matchingCostCenterIds)
                    ->orWhereIn('management_allowance_cost_center_5', $matchingCostCenterIds)
                    ->orWhereIn('extra_pay_1_cost_center_6', $matchingCostCenterIds)
                    ->orWhereIn('extra_pay_2_cost_center_7', $matchingCostCenterIds);
            });
        }
        
        // For project coordinators with optional cost center exclusion
        $excludedProjectCoordinatorIds = $excludePermissions['excluded_project_coordinator_ids'] ?? [];
        
        $hasProjectCoordinatorRole = CostCenter::where('project_coordinator_user_id', $user->id)
            ->when(!empty($excludedProjectCoordinatorIds), function($query) use ($excludedProjectCoordinatorIds) {
                return $query->whereNotIn('id', $excludedProjectCoordinatorIds);
            })
            ->exists() || 
            $delegations->contains(function ($delegation) use ($excludedProjectCoordinatorIds) {
                if (strpos($delegation->type, 'project_coordinator_workgroup_') !== 0) {
                    return false;
                }
                $originalUserId = $delegation->original_user_id;
                return CostCenter::where('project_coordinator_user_id', $originalUserId)
                    ->when(!empty($excludedProjectCoordinatorIds), function($query) use ($excludedProjectCoordinatorIds) {
                        return $query->whereNotIn('id', $excludedProjectCoordinatorIds);
                    })
                    ->exists();
            });
                
        if (!in_array('project_coordinator', $excludePermissions) && $hasProjectCoordinatorRole) {
            
            $projectCoordinatorCostCenters = CostCenter::where('project_coordinator_user_id', $user->id)
                ->when(!empty($excludedProjectCoordinatorIds), function($query) use ($excludedProjectCoordinatorIds) {
                    return $query->whereNotIn('id', $excludedProjectCoordinatorIds);
                })
                ->get();

            $delegateOriginalUserIds = $delegations->filter(function ($delegation) {
                return strpos($delegation->type, 'project_coordinator_workgroup_') === 0;
            })->map(function ($delegation) {
                return $delegation->original_user_id;
            })->unique();
            
            $delegateCostCenters = collect();
            foreach ($delegateOriginalUserIds as $originalUserId) {
                $costcenters = CostCenter::where('project_coordinator_user_id', $originalUserId)
                    ->when(!empty($excludedProjectCoordinatorIds), function($query) use ($excludedProjectCoordinatorIds) {
                        return $query->whereNotIn('id', $excludedProjectCoordinatorIds);
                    })
                    ->get();
                if ($costcenters->isNotEmpty()) {
                    $delegateCostCenters = $delegateCostCenters->merge($costcenters);
                }
            }

            $allRelevantCostCenters = $projectCoordinatorCostCenters->merge($delegateCostCenters);
            $matchingCostCenterIds = $allRelevantCostCenters->pluck('id')->toArray();

            $orQueries->push(function ($query) use ($matchingCostCenterIds) {
                $query->whereIn('base_salary_cost_center_1', $matchingCostCenterIds)
                    ->orWhereIn('base_salary_cost_center_2', $matchingCostCenterIds)
                    ->orWhereIn('base_salary_cost_center_3', $matchingCostCenterIds)
                    ->orWhereIn('health_allowance_cost_center_4', $matchingCostCenterIds)
                    ->orWhereIn('management_allowance_cost_center_5', $matchingCostCenterIds)
                    ->orWhereIn('extra_pay_1_cost_center_6', $matchingCostCenterIds)
                    ->orWhereIn('extra_pay_2_cost_center_7', $matchingCostCenterIds);
            });
        }
        
        // For post financing approvers
        $hasPostFinancingRole = $user && $user->hasRole('utofinanszirozas_fedezetigazolo') ||
            $delegations->contains(function ($delegation) {
                return $delegation->type === 'post_financing_approver';
            });
            
        if (!in_array('post_financing_approver', $excludePermissions) && $hasPostFinancingRole) {

            $orQueries->push(function ($query) {
                $query->whereJsonLength('meta_data->additional_fields', '>', 0)
                    ->whereJsonContains('meta_data->additional_fields', ['post_financed_application' => 'on']);
            });
        }

        // suspended by me
        $orQueries->push(function (Builder $query) use ($user) {
            $query->where('state', 'suspended')
                  ->where('updated_by', $user->id);
        });

        if ($orQueries->isEmpty()) {
            // return no rows
            return self::query()->whereRaw('1 = 0');
        }

        foreach ($orQueries as $orQuery) {
            $recruitmentsQuery->orWhere($orQuery);
        }
        
        return $recruitmentsQuery;
    }

    /*protected static function newFactory()
    {
        return RecruitmentWorkflowFactory::new();
    }*/

    protected $table = 'recruitment_workflow';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fillable = array_merge($this->fillable, [
            'name',
            'birth_date',
            'social_security_number',
            'address',
            'job_ad_exists',
            'applicants_female_count',
            'applicants_male_count',
            'has_prior_employment',
            'has_current_volunteer_contract',
            'is_retired',
            'citizenship',
            'workgroup_id_1',
            'workgroup_id_2',
            'position_id',
            'job_description',
            'employment_type',
            'task',
            'employment_start_date',
            'employment_end_date',
            'employer_contribution',
            'base_salary_cost_center_1',
            'base_salary_monthly_gross_1',
            'base_salary_cost_center_2',
            'base_salary_monthly_gross_2',
            'base_salary_cost_center_3',
            'base_salary_monthly_gross_3',
            'health_allowance_cost_center_4',
            'health_allowance_monthly_gross_4',
            'management_allowance_cost_center_5',
            'management_allowance_monthly_gross_5',
            'management_allowance_end_date',
            'extra_pay_1_cost_center_6',
            'extra_pay_1_monthly_gross_6',
            'extra_pay_1_end_date',
            'extra_pay_2_cost_center_7',
            'extra_pay_2_monthly_gross_7',
            'extra_pay_2_end_date',
            'weekly_working_hours',
            'work_start_monday',
            'work_end_monday',
            'work_start_tuesday',
            'work_end_tuesday',
            'work_start_wednesday',
            'work_end_wednesday',
            'work_start_thursday',
            'work_end_thursday',
            'work_start_friday',
            'work_end_friday',
            'email',
            'entry_permissions',
            'license_plate',
            'employee_room',
            'phone_extension',
            'external_access_rights',
            'required_tools',
            'available_tools',
            'inventory_numbers_of_available_tools',
            'personal_data_sheet',
            'student_status_verification',
            'certificates',
            'requires_commute_support',
            'commute_support_form',
            'probation_period',
            'contract',
            'contract_registration_number',
            'obligee_number',
            'comment',
            'external_privileges',
            'medical_eligibility_data'
        ]);

        $this->casts = array_merge($this->casts, [
            'job_ad_exists' => 'boolean',
            'has_prior_employment' => 'boolean',
            'has_current_volunteer_contract' => 'boolean',
            'is_retired' => 'boolean',
            'requires_commute_support' => 'boolean',
            'employer_contribution' => 'decimal:1',
        ]);

        $this->attributes = array_merge($this->attributes, [
            'state' => 'new_request',
            'job_ad_exists' => true,
        ]);
    }

    public function initiator_institute()
    {
        return $this->belongsTo(Institute::class, 'initiator_institute_id');
    }

    public function workgroup1()
    {
        return $this->belongsTo(Workgroup::class, 'workgroup_id_1');
    }

    public function workgroup2()
    {
        return $this->belongsTo(Workgroup::class, 'workgroup_id_2');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function base_salary_cc1()
    {
        return $this->belongsTo(CostCenter::class, 'base_salary_cost_center_1');
    }

    public function base_salary_cc2()
    {
        return $this->belongsTo(CostCenter::class, 'base_salary_cost_center_2');
    }

    public function base_salary_cc3()
    {
        return $this->belongsTo(CostCenter::class, 'base_salary_cost_center_3');
    }

    public function health_allowance_cc()
    {
        return $this->belongsTo(CostCenter::class, 'health_allowance_cost_center_4');
    }

    public function management_allowance_cc()
    {
        return $this->belongsTo(CostCenter::class, 'management_allowance_cost_center_5');
    }

    public function extra_pay_1_cc()
    {
        return $this->belongsTo(CostCenter::class, 'extra_pay_1_cost_center_6');
    }

    public function extra_pay_2_cc()
    {
        return $this->belongsTo(CostCenter::class, 'extra_pay_2_cost_center_7');
    }
}
