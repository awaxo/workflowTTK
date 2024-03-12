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
            $table->string('workgroup_number', 5)->after('email')->nullable();
            $table->foreign('workgroup_number')->references('workgroup_number')->on('wf_workgroup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wf_user', function (Blueprint $table) {
            $table->dropForeign(['workgroup_number']);
            $table->dropColumn('workgroup_number');
        });
    }
};
