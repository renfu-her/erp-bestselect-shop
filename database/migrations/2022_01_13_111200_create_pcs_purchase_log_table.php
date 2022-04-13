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
            $table->integer('event_parent_id')->comment('事件上層ID 採購pcs_purchase.id、寄倉csn_consignment.id');
            $table->integer('product_style_id')->nullable()->comment('款式ID');
            $table->string('event')->comment('事件 採購purchase、寄倉consignment');
            $table->string('event_id')->comment('事件ID 採購pcs_purchase_items.id、寄倉csn_consignment_items.id；；若沒產品款式ID 則同event_parent_id；；入庫時改存入庫單pcs_purchase_inbound.id');
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
