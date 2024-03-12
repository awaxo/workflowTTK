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
        Schema::create('wf_room', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workgroup_id');
            $table->string('room_number', 50);
            $table->timestamps();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');

            $table->foreign('workgroup_id')->references('id')->on('wf_workgroup');
            $table->foreign('created_by')->references('id')->on('wf_user');
            $table->foreign('updated_by')->references('id')->on('wf_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wf_room');
    }
};
