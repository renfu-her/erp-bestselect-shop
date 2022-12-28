<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColOnePageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opg_one_page', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('名稱');
            $table->integer('collection_id')->comment('群組id');
            $table->integer('sale_channel_id')->comment('通路id');
            $table->tinyInteger('online_pay')->default(1)->comment('線上付款');
            $table->tinyInteger('active')->default(1)->comment('啟用');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opg_one_page');
    }
}
