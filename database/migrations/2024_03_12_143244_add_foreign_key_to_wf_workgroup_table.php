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
        Schema::table('wf_workgroup', function (Blueprint $table) {
            $table->foreign('labor_administrator')->references('id')->on('wf_labor_administrator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wf_workgroup', function (Blueprint $table) {
            $table->dropForeign(['labor_administrator']);
        });
    }
};
