<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdxTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('idx_template', function (Blueprint $table) {
            $table->id()->comment('版型ID');
            $table->string('title')->comment('大標題');
            $table->integer('group_id')->nullable()->comment('商品群組id');
            $table->tinyInteger('style_type')->nullable()->comment('樣式 1:樣式一（左右滑動） 2:... 3:...');
            $table->tinyInteger('is_public')->default(0)->comment('開關 0:false 1:true');
            $table->integer('sort')->default(100)->comment('排序');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('idx_template');
    }
}
