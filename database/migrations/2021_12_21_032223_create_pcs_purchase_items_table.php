<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePcsPurchaseItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pcs_purchase_items', function (Blueprint $table) {
            $table->id()->comment('採購明細id');
            $table->string('ps_id')->comment('款式product_style_id 帶出款式sku碼');
            $table->string('price')->comment('單價');
            $table->integer('count')->comment('數量');
            $table->tinyInteger('inbound_status')->default(0)->comment('入庫狀態 0:未入庫');
            $table->dateTime('inbound_date')->nullable()->comment('入庫日期');
            $table->integer('inbound_count')->default(0)->comment('入庫數量');
            $table->integer('sale_count')->default(0)->comment('銷售數量 出貨時做計算 (出貨時跳出採購單，讓人員選擇要從哪一筆出貨)');
            $table->integer('error_count')->default(0)->comment('異常數量');
            $table->dateTime('expiry_date')->comment('有效期限');
            $table->integer('depot_id')->comment('倉庫');
            $table->integer('inbound_id')->nullable()->comment('入庫者');
            $table->integer('temp_id')->comment('物流類型 寄來使用何種溫層');
            $table->tinyInteger('status')->default(0)->comment('狀態(0:正常/1:短缺/2:溢出)');
            $table->string('memo')->comment('備註');
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
        Schema::dropIfExists('pcs_purchase_items');
    }
}
