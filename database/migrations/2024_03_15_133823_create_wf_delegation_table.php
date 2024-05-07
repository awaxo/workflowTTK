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
        Schema::create('wf_delegation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_user_id')->constrained('wf_user');
            $table->foreignId('delegate_user_id')->constrained('wf_user');
            $table->string('type');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->tinyInteger('deleted')->unsigned()->default(0);
            $table->timestamps();
            $table->foreignId('created_by')->constrained('wf_user');
            $table->foreignId('updated_by')->constrained('wf_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wf_delegation');
    }
};
