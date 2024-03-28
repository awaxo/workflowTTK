<?php

namespace Database\Migrations\Traits;

use Illuminate\Database\Schema\Blueprint;

trait GenericWorkflow
{
    protected function addGenericFields(Blueprint $table, string $default_state = 'new_request')
    {
        $table->id();
        $table->unsignedBigInteger('workflow_type_id');
        $table->unsignedInteger('workflow_deadline')->nullable();
        $table->string('state')->default($default_state);
        $table->unsignedBigInteger('initiator_workgroup_id')->nullable();
        $table->string('meta_key')->nullable();
        $table->text('meta_value')->nullable();
        $table->tinyInteger('deleted')->unsigned()->default(0);
        $table->timestamps();
        $table->unsignedBigInteger('created_by');
        $table->unsignedBigInteger('updated_by');

        $table->foreign('workflow_type_id')->references('id')->on('wf_workflow_type');
        $table->foreign('initiator_workgroup_id')->references('id')->on('wf_workgroup');
        $table->foreign('created_by')->references('id')->on('wf_user');
        $table->foreign('updated_by')->references('id')->on('wf_user');
    }
}