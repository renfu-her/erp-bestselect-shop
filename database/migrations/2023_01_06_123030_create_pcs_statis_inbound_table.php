<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePcsStatisInboundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pcs_statis_inbound', function (Blueprint $table) {
            $table->id()->comment('庫存ID');
            $table->string('event')->comment('事件 採購purchase、寄倉consignment');
            $table->unsignedBigInteger('product_style_id')->comment('商品款式ID');
            $table->unsignedBigInteger('depot_id')->comment('倉庫ID');
            $table->integer('qty')->default(0)->comment('庫存數量');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pcs_statis_inbound');
    }
}
