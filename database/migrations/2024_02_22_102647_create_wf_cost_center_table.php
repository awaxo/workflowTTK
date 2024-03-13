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
        Schema::create('wf_cost_center', function (Blueprint $table) {
            $table->id();
            $table->string('cost_center_code', 50);
            $table->string('name');
            $table->string('type', 100);
            $table->unsignedBigInteger('lead_user_id');
            $table->unsignedBigInteger('project_coordinator_user_id');
            $table->date('due_date');
            $table->decimal('minimal_order_limit', 10, 2);
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->timestamps();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');

            $table->foreign('created_by')->references('id')->on('wf_user');
            $table->foreign('updated_by')->references('id')->on('wf_user');
            $table->foreign('lead_user_id')->references('id')->on('wf_user');
            $table->foreign('project_coordinator_user_id')->references('id')->on('wf_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wf_cost_center');
    }
};
