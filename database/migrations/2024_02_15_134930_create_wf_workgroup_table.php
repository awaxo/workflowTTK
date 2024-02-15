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
        Schema::create('wf_workgroup', function (Blueprint $table) {
            $table->id();
            $table->string('workgroup_number', 100)->unique();
            $table->string('name', 255);
            $table->unsignedBigInteger('leader');
            $table->unsignedBigInteger('labor_administrator');
            $table->tinyInteger('deleted')->unsigned()->default(0);
            $table->timestamps();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');

            // Foreign keys
            $table->foreign('leader')->references('id')->on('wf_user');
            $table->foreign('labor_administrator')->references('id')->on('wf_user');
            $table->foreign('created_by')->references('id')->on('wf_user');
            $table->foreign('updated_by')->references('id')->on('wf_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wf_workgroup');
    }
};
