<?php

namespace Modules\EmployeeRecruitment\tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

class RecruitmentWorkflowTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected function createState(string $state)
    {
        $workflow = RecruitmentWorkflow::factory()->create();
        $workflow->state = $state;
        return $workflow;
    }

    protected function assertTransition(string $fromState, string $transition, string $toState)
    {
        $workflow = $this->createState($fromState);
        $workflow->workflow_apply($transition);
        $workflow->save();
        $this->assertEquals($toState, $workflow->refresh()->state);
    }

    /** @test */
    public function it_transitions_from_new_request_to_it_head_approval()
    {
        $this->assertTransition('new_request', 'to_it_head_approval', 'it_head_approval');
    }

    /** @test */
    public function it_transitions_from_it_head_approval_to_supervisor_approval()
    {
        $this->assertTransition('it_head_approval', 'to_supervisor_approval', 'supervisor_approval');
    }

    /** @test */
    public function it_transitions_from_supervisor_approval_to_group_lead_approval()
    {
        $this->assertTransition('supervisor_approval', 'to_group_lead_approval', 'group_lead_approval');
    }

    /** @test */
    public function it_transitions_from_group_lead_approval_to_director_approval()
    {
        $this->assertTransition('group_lead_approval', 'to_director_approval', 'director_approval');
    }

    /** @test */
    public function it_transitions_from_director_approval_to_hr_lead_approval()
    {
        $this->assertTransition('director_approval', 'to_hr_lead_approval', 'hr_lead_approval');
    }

    /** @test */
    public function it_transitions_from_hr_lead_approval_to_proof_of_coverage()
    {
        $this->assertTransition('hr_lead_approval', 'to_proof_of_coverage', 'proof_of_coverage');
    }

    /** @test */
    public function it_transitions_from_proof_of_coverage_to_project_coordination_lead_approval()
    {
        $this->assertTransition('proof_of_coverage', 'to_project_coordination_lead_approval', 'project_coordination_lead_approval');
    }

    /** @test */
    public function it_transitions_from_project_coordination_lead_approval_to_post_financing_approval()
    {
        $this->assertTransition('project_coordination_lead_approval', 'to_post_financing_approval', 'post_financing_approval');
    }

    /** @test */
    public function it_transitions_from_post_financing_approval_to_registration()
    {
        $this->assertTransition('post_financing_approval', 'to_registration', 'registration');
    }

    /** @test */
    public function it_transitions_from_registration_to_financial_counterparty_approval()
    {
        $this->assertTransition('registration', 'to_financial_counterparty_approval', 'financial_counterparty_approval');
    }

    /** @test */
    public function it_transitions_from_financial_counterparty_approval_to_obligee_approval()
    {
        $this->assertTransition('financial_counterparty_approval', 'to_obligee_approval', 'obligee_approval');
    }

    /** @test */
    public function it_transitions_from_obligee_approval_to_draft_contract_pending()
    {
        $this->assertTransition('obligee_approval', 'to_draft_contract_pending', 'draft_contract_pending');
    }

    /** @test */
    public function it_transitions_from_draft_contract_pending_to_financial_countersign_approval()
    {
        $this->assertTransition('draft_contract_pending', 'to_financial_countersign_approval', 'financial_countersign_approval');
    }

    /** @test */
    public function it_transitions_from_financial_countersign_approval_to_obligee_signature()
    {
        $this->assertTransition('financial_countersign_approval', 'to_obligee_signature', 'obligee_signature');
    }

    /** @test */
    public function it_transitions_from_obligee_signature_to_employee_signature()
    {
        $this->assertTransition('obligee_signature', 'to_employee_signature', 'employee_signature');
    }

    /** @test */
    public function it_transitions_from_employee_signature_to_request_to_complete()
    {
        $this->assertTransition('employee_signature', 'to_request_to_complete', 'request_to_complete');
    }

    /** @test */
    public function it_transitions_from_request_to_complete_to_completed()
    {
        $this->assertTransition('request_to_complete', 'to_completed', 'completed');
    }

    /** @test */
    public function it_transitions_to_suspended_from_all_applicable_states()
    {
        $applicableStates = [
            'new_request', 'it_head_approval', 'supervisor_approval', 'group_lead_approval',
            'director_approval', 'hr_lead_approval', 'proof_of_coverage', 'project_coordination_lead_approval',
            'post_financing_approval', 'registration', 'financial_counterparty_approval', 'obligee_approval',
            'draft_contract_pending', 'financial_countersign_approval', 'obligee_signature', 'employee_signature',
            'request_to_complete', 'request_review',
        ];

        foreach ($applicableStates as $state) {
            $workflow = $this->createState($state);
            $workflow->workflow_apply('to_suspended');
            $workflow->save();
            $this->assertEquals('suspended', $workflow->refresh()->state);
        }
    }

    /** @test */
    public function it_transitions_to_request_review_from_all_applicable_states_except_completed_and_suspended()
    {
        $applicableStates = [
            'new_request', 'it_head_approval', 'supervisor_approval', 'group_lead_approval',
            'director_approval', 'hr_lead_approval', 'proof_of_coverage', 'project_coordination_lead_approval',
            'post_financing_approval', 'registration', 'financial_counterparty_approval', 'obligee_approval',
            'draft_contract_pending', 'financial_countersign_approval', 'obligee_signature', 'employee_signature',
            'request_to_complete',
        ];

        foreach ($applicableStates as $state) {
            $workflow = $this->createState($state);
            $workflow->workflow_apply('to_request_review');
            $workflow->save();
            $this->assertEquals('request_review', $workflow->refresh()->state);
        }
    }

    /** @test */
    public function it_resumes_from_suspended_to_all_states_except_completed_and_suspended()
    {
        $resumableStates = [
            'new_request', 'it_head_approval', 'supervisor_approval', 'group_lead_approval',
            'director_approval', 'hr_lead_approval', 'proof_of_coverage', 'project_coordination_lead_approval',
            'post_financing_approval', 'registration', 'financial_counterparty_approval', 'obligee_approval',
            'draft_contract_pending', 'financial_countersign_approval', 'obligee_signature', 'employee_signature',
            'request_to_complete', 'request_review',
        ];

        $suspendedWorkflow = $this->createState('suspended');

        foreach ($resumableStates as $state) {
            if ($state !== 'completed' && $state !== 'suspended') {
                $workflow = clone $suspendedWorkflow;
                $workflow->workflow_apply('resume_from_suspended');
                $workflow->state = $state;
                $workflow->save();
                $this->assertEquals($state, $workflow->refresh()->state);
            }
        }
    }
}
