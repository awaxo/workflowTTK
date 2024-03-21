<?php

namespace Modules\EmployeeRecruitment\Database\Seeders;

use App\Models\WorkflowType;
use App\Models\Workgroup;
use Illuminate\Database\Seeder;

class RecruitmentWorkflowTypeSeeder extends Seeder
{
    public function run()
    {
        // Create a new workflow type for the recruitment process
        $workgroup = Workgroup::where('workgroup_number', 908)->first();
        WorkflowType::firstOrCreate([
            'name' => 'Felvételi kérelem folyamata',
            'description' => 'A felvételi kérelem folyamata',
            'manager_workgroup_id' => $workgroup->id,
            'created_by' => 1,
            'updated_by' => 1,
        ]);
    }
}