<?php
// filepath: /c:/Munka/TTK/workflowTTK/database/migrations/2025_02_11_104303_add_featured_to_wf_user_table.php

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
            $table->boolean('featured')->default(false)->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wf_user', function (Blueprint $table) {
            $table->dropColumn('featured');
        });
    }
};