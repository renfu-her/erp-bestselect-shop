<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePcsPurchaseInboundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pcs_purchase_inbound', function (Blueprint $table) {
            $table->id()->comment('採購入庫id');
            $table->string('sn', 20)->comment('入庫單SN');
            $table->string('event')->comment('事件 採購 寄倉');
            $table->string('event_id')->comment('事件ID');
            $table->integer('event_item_id')->comment('採購款式id');
            $table->integer('product_style_id')->comment('款式product_style_id 帶出款式sku碼');
            $table->dateTime('expiry_date')->nullable()->comment('有效期限');
            $table->dateTime('inbound_date')->nullable()->comment('入庫日期');
            $table->integer('inbound_num')->default(0)->comment('入庫數量');
            $table->integer('depot_id')->nullable()->comment('倉庫');
            $table->string('depot_name', 30)->nullable()->comment('倉庫名稱');
            $table->integer('inbound_user_id')->nullable()->comment('入庫者');
            $table->string('inbound_user_name', 20)->nullable()->comment('入庫者名稱');
            $table->integer('sale_num')->default(0)->comment('銷售數量 出貨時做計算');
            $table->integer('csn_num')->default(0)->comment('寄倉數量 寄倉時加 寄倉退回後減，並加回入庫數量');
            $table->integer('consume_num')->default(0)->comment('被當耗材數量');
            $table->unsignedBigInteger('origin_inbound_id')->nullable()->comment('原始入庫來源ID');

            $table->dateTime('close_date')->nullable()->comment('結單日期');
            $table->string('memo')->nullable()->comment('備註');
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
        Schema::dropIfExists('pcs_purchase_inbound');
    }
}
