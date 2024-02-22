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
        Schema::create('recruit_workflow_step_rights', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_step_id');
            $table->unsignedBigInteger('role_id');
            $table->text('custom_approval_rules')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');

            //$table->foreign('workflow_step_id')->references('id')->on('wf_workflow_step');
            $table->foreign('role_id')->references('id')->on('wf_role');
            $table->foreign('created_by')->references('id')->on('wf_user');
            $table->foreign('updated_by')->references('id')->on('wf_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruit_workflow_step_rights');
    }
};
