<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePcsPurchaseLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pcs_purchase_log', function (Blueprint $table) {
            $table->id()->comment('logID');
            $table->integer('event_parant_id')->comment('事件上層ID 採購 寄賣');
            $table->integer('product_style_id')->nullable()->comment('款式ID');
            $table->string('event')->comment('事件');
            $table->string('event_id')->comment('事件ID');
            $table->string('feature')->comment('功能');
            $table->integer('qty')->nullable()->comment('數量');
            $table->integer('user_id')->comment('操作者');
            $table->string('user_name')->comment('操作者名稱');
            $table->string('note')->nullable()->comment('備註');
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
        Schema::dropIfExists('pcs_purchase_log');
    }
}
