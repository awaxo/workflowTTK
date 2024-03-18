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
        Schema::create('wf_user_workgroup', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('workgroup_id');
            $table->tinyInteger('is_lead')->unsigned()->nullable();
            $table->tinyInteger('deleted')->unsigned()->default(0);
            $table->timestamps();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
    
            $table->foreign('user_id')->references('id')->on('wf_user');
            $table->foreign('workgroup_id')->references('id')->on('wf_workgroup');
            $table->foreign('created_by')->references('id')->on('wf_user');
            $table->foreign('updated_by')->references('id')->on('wf_user');

            $table->primary(['user_id', 'workgroup_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wf_user_workgroup');
    }
};
