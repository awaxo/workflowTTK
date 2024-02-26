<?php

namespace Modules\EmployeeRecruitment\tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Country;
use App\Models\Workgroup;
use App\Models\Position;
use App\Models\WorkflowType;
use Modules\EmployeeRecruitment\App\Models\RecruitmentCostCenter;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

class RecruitmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_recruit_workflow_can_be_created_with_all_fields()
    {
        $workflowType = WorkflowType::factory()->create();
        $citizenship = Country::factory()->create();
        $workgroup1 = Workgroup::factory()->create();
        $workgroup2 = Workgroup::factory()->create();
        $position = Position::factory()->create();
        $costCenter1 = RecruitmentCostCenter::factory()->create();
        $createdBy = User::factory()->create();
        $updatedBy = User::factory()->create();

        $recruitWorkflow = RecruitmentWorkflow::factory()->create([
            'workflow_type_id' => $workflowType->id,
            'applicants_female_count' => 5,
            'applicants_male_count' => 10,
            'has_prior_employment' => true,
            'has_current_volunteer_contract' => false,
            'citizenship_id' => $citizenship->id,
            'workgroup_id_1' => $workgroup1->id,
            'workgroup_id_2' => $workgroup2->id,
            'position_id' => $position->id,
            'job_description' => 'A detailed job description',
            'employment_type' => 'Permanent',
            'task' => 'A detailed task list',
            'employment_start_date' => '2023-01-01',
            'employment_end_date' => '2023-12-31',
            'base_salary_cost_center_1' => $costCenter1->id,
            'base_salary_monthly_gross_1' => 5000.00,
            'weekly_working_hours' => 40,
            'work_start_monday' => '08:00:00',
            'work_end_monday' => '17:00:00',
            'email' => 'test@example.com',
            'entry_permissions' => 'General access',
            'phone_extension' => 1234,
            'work_with_radioactive_isotopes' => false,
            'work_with_carcinogenic_materials' => false,
            'personal_data_sheet' => 'Yes',
            'certificates' => 'Required',
            'requires_commute_support' => true,
            'created_by' => $createdBy->id,
            'updated_by' => $updatedBy->id,
        ]);

        $this->assertDatabaseHas('recruitment_workflow', [
            'workflow_type_id' => $workflowType->id,
            'workflow_deadline' => 24,
            'state' => 'new_request',
            'job_ad_exists' => true,
            'applicants_female_count' => 5,
            'applicants_male_count' => 10,
            'has_prior_employment' => true,
            'citizenship_id' => $citizenship->id,
            'workgroup_id_1' => $workgroup1->id,
            'position_id' => $position->id,
            'job_description' => 'A detailed job description',
            'employment_type' => 'Permanent',
            'weekly_working_hours' => 40,
            'email' => 'test@example.com',
            'phone_extension' => 1234,
            'personal_data_sheet' => 'Yes',
            'certificates' => 'Required',
            'requires_commute_support' => true,
            'created_by' => $createdBy->id,
            'updated_by' => $updatedBy->id,
        ]);
    }

    /** @test */
    public function a_recruit_workflow_can_be_updated()
    {
        $recruitWorkflow = RecruitmentWorkflow::factory()->create();

        $newData = [
            'state' => 'it_head_approval',
            'job_ad_exists' => false,
            'applicants_female_count' => 2,
            'employment_type' => 'Temporary',
            'weekly_working_hours' => 35,
            'phone_extension' => 4321,
            'requires_commute_support' => false,
        ];

        $recruitWorkflow->update($newData);

        $this->assertDatabaseHas('recruitment_workflow', $newData + ['id' => $recruitWorkflow->id]);
    }

    /** @test */
    public function workflow_relationships_are_accessible()
    {
        $workflow = RecruitmentWorkflow::factory()->create();

        $this->assertNotNull($workflow->workflowType);
        $this->assertNotNull($workflow->createdBy);
        $this->assertNotNull($workflow->updatedBy);
        $this->assertNotNull($workflow->citizenship);
        $this->assertNotNull($workflow->workgroup1);
        $this->assertNotNull($workflow->workgroup2);
        $this->assertNotNull($workflow->position);
        $this->assertNotNull($workflow->base_salary_cost_center_1);
    }
}
