<?php

namespace Modules\EmployeeRecruitment\Database\Seeders;

use App\Models\WorkflowType;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RecruitmentWorkflowTypeSeeder extends Seeder
{
    public function run()
    {
        WorkflowType::firstOrCreate(['name' => 'Felvételi kérelem folyamata']);
    }
}