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
            $table->integer('purchase_id')->comment('採購id');
            $table->integer('product_style_id')->comment('款式product_style_id 帶出款式sku碼');
            $table->dateTime('expiry_date')->nullable()->comment('有效期限');
            $table->tinyInteger('status')->default(0)->comment('狀態(0:尚未入庫/1:正常/2:短缺/3:溢出)');
            $table->dateTime('inbound_date')->nullable()->comment('入庫日期');
            $table->integer('inbound_num')->default(0)->comment('入庫數量');
            $table->integer('error_num')->default(0)->comment('異常數量');
            $table->integer('depot_id')->nullable()->comment('倉庫');
            $table->integer('inbound_user_id')->nullable()->comment('入庫者');
            $table->integer('sale_num')->default(0)->comment('銷售數量 出貨時做計算 (出貨時跳出採購單，讓人員選擇要從哪一筆出貨)');
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
