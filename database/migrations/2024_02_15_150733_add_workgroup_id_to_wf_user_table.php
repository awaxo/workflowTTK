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
        Schema::table('wf_user', function (Blueprint $table) {
            $table->unsignedBigInteger('workgroup_id')->after('email')->nullable();
            
            $table->foreign('workgroup_id')->references('id')->on('wf_workgroup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wf_user', function (Blueprint $table) {
            $table->dropForeign(['workgroup_id']);
            $table->dropColumn('workgroup_id');
        });
    }
};
