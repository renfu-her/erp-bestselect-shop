<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ord_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_sn')->comment('訂單流水號');
            $table->integer('sale_channel_id')->comment('銷售通路id');
            $table->integer('payment_method')->comment('付款方式');
            $table->integer('payment_id')->nullable()->comment('付款單id,有值完成');
            $table->string('ord_email')->comment('訂購人帳號');
            $table->string('ord_name')->comment('訂購人姓名');
            $table->string('ord_phone')->comment('訂購人電話');
            $table->string('ord_address')->comment('訂購人地址');
            $table->string('rec_name')->comment('收件人姓名');
            $table->string('rec_phone')->comment('收件人電話');
            $table->string('rec_address')->comment('收件人地址');
            $table->integer('total_price')->comment('總費用');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('ord_sub_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->comment('訂單id');
            $table->string('order_sn')->comment('訂單流水號');
            $table->string('shipment_sn')->comment('物流流水號');
            $table->integer('shipment_id')->comment('運費id');
            $table->string('shipment_dlv_fee')->comment('運費');
            $table->string('shipment_method')->comment('出貨方式');
            $table->string('shipment_temp')->comment('溫層');
            $table->integer('total_price')->comment('總費用');
            $table->softDeletes();
        });

        Schema::create('ord_items', function (Blueprint $table) {
            $table->id();
            $table->integer('sub_order_id')->comment('訂單id');
            $table->string('sku')->comment('商品sku');
            $table->string('product_name')->comment('商品名稱');
            $table->integer('price')->comment('單價');
            $table->integer('qty')->comment('數量');
            $table->integer('total_price')->comment('小計');
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
        Schema::dropIfExists('ord_orders');
        Schema::dropIfExists('ord_sub_orders');
        Schema::dropIfExists('ord_items');

    }
}
