<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recruitment_workflow', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_id');
            $table->string('state')->default('new_request');
            $table->unsignedTinyInteger('job_ad_exists')->default(1);
            $table->unsignedInteger('applicants_female_count');
            $table->unsignedInteger('applicants_male_count');
            $table->unsignedTinyInteger('has_prior_employment');
            $table->unsignedTinyInteger('has_current_volunteer_contract');
            $table->unsignedBigInteger('citizenship_id');
            $table->unsignedBigInteger('workgroup_id_1');
            $table->unsignedBigInteger('workgroup_id_2')->nullable();
            $table->unsignedBigInteger('position_id');
            $table->string('job_description', 500);
            $table->string('employment_type', 50);
            $table->string('task', 1000);
            $table->date('employment_start_date');
            $table->date('employment_end_date');
            $table->unsignedBigInteger('base_salary_cost_center_1');
            $table->decimal('base_salary_monthly_gross_1', 10, 2);
            $table->unsignedBigInteger('base_salary_cost_center_2')->nullable();
            $table->decimal('base_salary_monthly_gross_2', 10, 2)->nullable();
            $table->unsignedBigInteger('base_salary_cost_center_3')->nullable();
            $table->decimal('base_salary_monthly_gross_3', 10, 2)->nullable();
            $table->unsignedBigInteger('health_allowance_cost_center_4')->nullable();
            $table->decimal('health_allowance_monthly_gross_4', 10, 2)->nullable();
            $table->unsignedBigInteger('management_allowance_cost_center_5')->nullable();
            $table->decimal('management_allowance_monthly_gross_5', 10, 2)->nullable();
            $table->date('management_allowance_end_date')->nullable();
            $table->unsignedBigInteger('extra_pay_1_cost_center_6')->nullable();
            $table->decimal('extra_pay_1_monthly_gross_6', 10, 2)->nullable();
            $table->date('extra_pay_1_end_date')->nullable();
            $table->unsignedBigInteger('extra_pay_2_cost_center_7')->nullable();
            $table->decimal('extra_pay_2_monthly_gross_7', 10, 2)->nullable();
            $table->date('extra_pay_2_end_date')->nullable();
            $table->unsignedInteger('weekly_working_hours');
            $table->time('work_start_monday');
            $table->time('work_end_monday');
            $table->time('work_start_tuesday');
            $table->time('work_end_tuesday');
            $table->time('work_start_wednesday');
            $table->time('work_end_wednesday');
            $table->time('work_start_thursday');
            $table->time('work_end_thursday');
            $table->time('work_start_friday');
            $table->time('work_end_friday');
            $table->string('email', 100);
            $table->text('entry_permissions');
            $table->string('license_plate', 15)->nullable();
            $table->string('employee_room')->nullable();
            $table->unsignedInteger('phone_extension');
            $table->string('required_tools')->nullable();
            $table->string('available_tools')->nullable();
            $table->string('inventory_numbers_of_available_tools', 1000)->nullable();
            $table->unsignedTinyInteger('work_with_radioactive_isotopes')->default(0);
            $table->unsignedTinyInteger('work_with_carcinogenic_materials')->default(0);
            $table->text('planned_carcinogenic_materials_use')->nullable();
            $table->string('personal_data_sheet');
            $table->string('student_status_verification')->nullable();
            $table->string('certificates');
            $table->unsignedTinyInteger('requires_commute_support')->nullable();
            $table->string('commute_support_form')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');

            $table->foreign('workflow_id')->references('id')->on('wf_workflow');
            $table->foreign('citizenship_id')->references('id')->on('wf_country');
            $table->foreign('workgroup_id_1')->references('id')->on('wf_workgroup');
            $table->foreign('workgroup_id_2')->references('id')->on('wf_workgroup');
            $table->foreign('position_id')->references('id')->on('wf_position');
            $table->foreign('base_salary_cost_center_1')->references('id')->on('recruitment_cost_center');
            $table->foreign('base_salary_cost_center_2')->references('id')->on('recruitment_cost_center');
            $table->foreign('base_salary_cost_center_3')->references('id')->on('recruitment_cost_center');
            $table->foreign('health_allowance_cost_center_4')->references('id')->on('recruitment_cost_center');
            $table->foreign('management_allowance_cost_center_5')->references('id')->on('recruitment_cost_center');
            $table->foreign('extra_pay_1_cost_center_6')->references('id')->on('recruitment_cost_center');
            $table->foreign('extra_pay_2_cost_center_7')->references('id')->on('recruitment_cost_center');
            $table->foreign('created_by')->references('id')->on('wf_user');
            $table->foreign('updated_by')->references('id')->on('wf_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_workflow');
    }
};
