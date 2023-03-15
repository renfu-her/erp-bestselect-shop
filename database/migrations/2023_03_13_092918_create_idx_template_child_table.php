<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdxTemplateChildTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('idx_template_child', function (Blueprint $table) {
            $table->id();
            $table->integer('template_id');
            $table->integer('group_id');
            $table->string('file')->nullable()->comment('圖片');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('idx_template_child');
    }
}
