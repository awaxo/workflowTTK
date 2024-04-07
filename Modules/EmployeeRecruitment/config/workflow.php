<?php

return [
    'recruitment_process' => [
        'type' => 'workflow',
        'marking_store' => [
            'type' => 'single_state',
            'property' => 'state',
        ],
        'metadata' => [
            'title' => 'Recruitment Process',
        ],
        'supports' => ['Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow'],
        'places' => [
            'new_request',
            'it_head_approval',
            'supervisor_approval',
            'group_lead_approval',
            'director_approval',
            'hr_lead_approval',
            'proof_of_coverage',
            'project_coordination_lead_approval',
            'post_financing_approval',
            'registration',
            'financial_counterparty_approval',
            'obligee_approval',
            'draft_contract_pending',
            'financial_countersign_approval',
            'obligee_signature',
            'employee_signature',
            'request_to_complete',
            'completed',
            'rejected',
            'suspended',
            'request_review',
        ],
        'transitions' => [
            'to_it_head_approval' => [
                'from' => ['new_request', 'request_review'],
                'to' => 'it_head_approval',
            ],
            'to_supervisor_approval' => [
                'from' => 'it_head_approval',
                'to' => 'supervisor_approval',
            ],
            'to_group_lead_approval' => [
                'from' => 'supervisor_approval',
                'to' => 'group_lead_approval',
            ],
            'to_director_approval' => [
                'from' => 'group_lead_approval',
                'to' => 'director_approval',
            ],
            'to_hr_lead_approval' => [
                'from' => 'director_approval',
                'to' => 'hr_lead_approval',
            ],
            'to_proof_of_coverage' => [
                'from' => 'hr_lead_approval',
                'to' => 'proof_of_coverage',
            ],
            'to_project_coordination_lead_approval' => [
                'from' => 'proof_of_coverage',
                'to' => 'project_coordination_lead_approval',
            ],
            'to_post_financing_approval' => [
                'from' => 'project_coordination_lead_approval',
                'to' => 'post_financing_approval',
            ],
            'to_registration' => [
                'from' => 'post_financing_approval',
                'to' => 'registration',
            ],
            'to_financial_counterparty_approval' => [
                'from' => 'registration',
                'to' => 'financial_counterparty_approval',
            ],
            'to_obligee_approval' => [
                'from' => 'financial_counterparty_approval',
                'to' => 'obligee_approval',
            ],
            'to_draft_contract_pending' => [
                'from' => 'obligee_approval',
                'to' => 'draft_contract_pending',
            ],
            'to_financial_countersign_approval' => [
                'from' => 'draft_contract_pending',
                'to' => 'financial_countersign_approval',
            ],
            'to_obligee_signature' => [
                'from' => 'financial_countersign_approval',
                'to' => 'obligee_signature',
            ],
            'to_employee_signature' => [
                'from' => 'obligee_signature',
                'to' => 'employee_signature',
            ],
            'to_request_to_complete' => [
                'from' => 'employee_signature',
                'to' => 'request_to_complete',
            ],
            'to_completed' => [
                'from' => 'request_to_complete',
                'to' => 'completed',
            ],
            'to_suspended' => [
                'from' => [
                    'new_request', 'it_head_approval', 'supervisor_approval', 'group_lead_approval',
                    'director_approval', 'hr_lead_approval', 'proof_of_coverage', 'project_coordination_lead_approval',
                    'post_financing_approval', 'registration', 'financial_counterparty_approval', 'obligee_approval',
                    'draft_contract_pending', 'financial_countersign_approval', 'obligee_signature', 'employee_signature',
                    'request_to_complete', 'request_review',
                ],
                'to' => 'suspended',
            ],
            'to_request_review' => [
                'from' => [
                    'new_request', 'it_head_approval', 'supervisor_approval', 'group_lead_approval',
                    'director_approval', 'hr_lead_approval', 'proof_of_coverage', 'project_coordination_lead_approval',
                    'post_financing_approval', 'registration', 'financial_counterparty_approval', 'obligee_approval',
                    'draft_contract_pending', 'financial_countersign_approval', 'obligee_signature', 'employee_signature',
                    'request_to_complete',
                ],
                'to' => 'request_review',
            ],
            'restore_from_suspended' => [
                'from' => 'suspended',
                'to' => [
                    'new_request', 'it_head_approval', 'supervisor_approval', 'group_lead_approval',
                    'director_approval', 'hr_lead_approval', 'proof_of_coverage', 'project_coordination_lead_approval',
                    'post_financing_approval', 'registration', 'financial_counterparty_approval', 'obligee_approval',
                    'draft_contract_pending', 'financial_countersign_approval', 'obligee_signature', 'employee_signature',
                    'request_to_complete', 'request_review',
                ],
            ],
        ],
    ],
];
