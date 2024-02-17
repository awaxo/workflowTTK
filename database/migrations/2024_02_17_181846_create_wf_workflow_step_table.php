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
        Schema::create('wf_workflow_step', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_step_type_id');
            $table->unsignedBigInteger('workflow_id');
            $table->unsignedInteger('step_deadline')->default(0);
            $table->string('meta_key')->nullable();
            $table->text('meta_value')->nullable();
            $table->tinyInteger('deleted')->unsigned()->default(0);
            $table->timestamps();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');

            $table->foreign('workflow_step_type_id')->references('id')->on('wf_workflow_step_type');
            $table->foreign('workflow_id')->references('id')->on('wf_workflow');
            $table->foreign('created_by')->references('id')->on('wf_user');
            $table->foreign('updated_by')->references('id')->on('wf_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wf_workflow_step');
    }
};
