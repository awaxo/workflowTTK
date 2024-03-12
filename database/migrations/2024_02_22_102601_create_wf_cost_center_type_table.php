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
        Schema::create('wf_cost_center_type', function (Blueprint $table) {
            $table->string('name')->primary();
            $table->boolean('tender');
            $table->unsignedBigInteger('financial_approver_role_id');
            $table->text('clause_template');
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->timestamps();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');

            $table->foreign('financial_approver_role_id')->references('id')->on('wf_role');
            $table->foreign('created_by')->references('id')->on('wf_user');
            $table->foreign('updated_by')->references('id')->on('wf_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wf_cost_center_type');
    }
};
