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
        Schema::create('wf_approver_substitute', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_approver_role_group_id');
            $table->unsignedBigInteger('substitute_role_group_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->tinyInteger('deleted')->unsigned()->default(0);
            $table->timestamps();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');

            // Foreign keys
            $table->foreign('original_approver_role_group_id')->references('id')->on('wf_role_group');
            $table->foreign('substitute_role_group_id')->references('id')->on('wf_role_group');
            $table->foreign('created_by')->references('id')->on('wf_user');
            $table->foreign('updated_by')->references('id')->on('wf_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wf_approver_substitute');
    }
};
