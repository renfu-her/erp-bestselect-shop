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
            $table->string('order_sn', 20)->comment('訂單流水號');
            $table->string('email', 100)->comment('訂購者email');
            $table->integer('sale_channel_id')->comment('銷售通路id');
            $table->integer('payment_method')->comment('付款方式');
            $table->integer('payment_id')->nullable()->comment('付款單id,有值完成');
            $table->string('status', 20)->nullable()->comment('訂單狀態');
            $table->integer('total_price')->comment('總金額');
            
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('ord_sub_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->comment('訂單id');
            $table->string('order_sn', 20)->comment('訂單流水號');
            $table->string('ship_sn', 20)->nullable()->comment('物流流水號');
            $table->string('ship_category', 30)->comment('物流類別');
            $table->string('ship_category_name', 30)->comment('物流類別');
            $table->string('ship_event', 30)->nullable()->comment('物流子項');
            $table->integer('ship_event_id')->nullable()->comment('物流子項id');
            $table->string('ship_temp', 10)->nullable()->comment('溫層');
            $table->integer('ship_temp_id')->nullable()->comment('溫層id');
            $table->integer('ship_rule_id')->nullable()->comment('減免id');
            $table->string('dlv_fee')->comment('運費');
            $table->string('status', 20)->comment('訂單狀態');
            $table->integer('total_price')->comment('總費用');
        });

        Schema::create('ord_items', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->comment('訂單id');
            $table->integer('sub_order_id')->comment('sub訂單id');
            $table->string('sku', 20)->comment('商品sku');
            $table->string('product_title', 40)->comment('商品名稱');
            $table->integer('price')->comment('單價');
            $table->integer('qty')->comment('數量');
            $table->string('type', 20)->nullable()->comment('商品/贈品');
            $table->integer('total_price')->comment('小計');
        });

        Schema::create('ord_address', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->comment('order id');
            $table->string('type', 10)->comment('收件者,寄件人,購買人');
            $table->integer('city_id')->comment('城市id');
            $table->string('city', 30)->comment('城市');
            $table->integer('region_id')->comment('區id');
            $table->string('region', 30)->comment('區');
            $table->string('addr', 50)->comment('地址短');
            $table->string('address', 100)->comment('地址');
            $table->string('zip', 5)->comment('郵遞區號');
            $table->string('name', 30)->comment('姓名');
            $table->string('phone', 20)->comment('電話');
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
        Schema::dropIfExists('ord_address');

    }
}
