<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('wf_position', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 100);
            $table->tinyInteger('deleted')->unsigned()->default(0);
            $table->timestamps();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');

            // Adjust foreign keys if necessary
            $table->foreign('created_by')->references('id')->on('wf_user');
            $table->foreign('updated_by')->references('id')->on('wf_user');
        });
    }

    public function down() {
        Schema::dropIfExists('wf_position');
    }
};
