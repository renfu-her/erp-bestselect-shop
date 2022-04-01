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
            $table->string('sn', 20)->comment('訂單流水號');
            $table->string('email', 100)->comment('訂購者email');
            $table->integer('sale_channel_id')->comment('銷售通路id');
            $table->string('status_code', 20)->nullable()->comment('訂單狀態代碼');
            $table->string('status', 20)->nullable()->comment('訂單狀態');
            $table->integer('rcode')->nullable()->comment('rcode消費者id');
            $table->integer('dlv_fee')->default(0)->comment('運費');
            $table->integer('origin_price')->default(0)->comment('小計'); 
            $table->integer('total_price')->default(0)->comment('總金額');
            $table->integer('discount_value')->default(0)->comment('折扣');
            $table->integer('discounted_price')->default(0)->comment('折扣後金額');
            $table->string('note')->nullable()->comment('備註');
            $table->timestamps();
        });

        Schema::create('ord_sub_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->comment('訂單id');
            $table->string('sn', 20)->comment('訂單流水號');
            $table->string('ship_sn', 20)->nullable()->comment('物流單流水號');
            $table->string('ship_category', 30)->comment('物流代碼');
            $table->string('ship_category_name', 30)->comment('物流類別名稱');
            $table->string('ship_event', 30)->nullable()->comment('物流子項');
            $table->integer('ship_event_id')->nullable()->comment('物流子項id');
            $table->string('ship_temp', 10)->nullable()->comment('溫層');
            $table->integer('ship_temp_id')->nullable()->comment('溫層id');
            $table->integer('ship_rule_id')->nullable()->comment('減免id');
            $table->string('package_sn', 30)->nullable()->comment('物流包裹編號SN');
            $table->unsignedBigInteger('actual_ship_group_id')->nullable()->comment('實際物流 出貨方式id 對應shi_group.id');
            $table->string('dlv_fee')->comment('運費');
            $table->string('status', 20)->comment('訂單狀態');
            $table->integer('total_price')->default(0)->comment('總費用');
            $table->integer('origin_price')->default(0)->comment('原始金額');
            $table->integer('discount_value')->default(0)->comment('優惠金額');
            $table->integer('discounted_price')->default(0)->comment('折扣後金額');

            $table->string('statu', 10)->nullable()->comment('物流狀態');
            $table->string('statu_code', 10)->nullable()->comment('物流狀態代碼');
        });

        Schema::create('ord_items', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->comment('訂單id');
            $table->integer('sub_order_id')->comment('sub訂單id');
            $table->unsignedBigInteger('product_style_id')->comment('商品款式ID');
            $table->string('sku', 20)->comment('商品sku');
            $table->string('product_title', 40)->comment('商品名稱');
            $table->integer('price')->comment('單價');
            $table->integer('qty')->comment('數量');
            $table->string('type', 20)->nullable()->comment('商品/贈品');
            $table->integer('origin_price')->default(0)->comment('原價');
            $table->integer('discount_value')->default(0)->comment('優惠金額');
            $table->integer('discounted_price')->default(0)->comment('折扣後金額');
        });

        Schema::create('ord_address', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->comment('order id');
            $table->string('type', 10)->comment('收件者,寄件人,購買人');
            $table->integer('city_id')->comment('城市id');
            $table->string('city_title', 30)->comment('城市');
            $table->integer('region_id')->comment('區id');
            $table->string('region_title', 30)->comment('區');
            $table->string('addr', 50)->comment('地址短');
            $table->string('address', 100)->comment('地址');
            $table->string('zipcode', 5)->comment('郵遞區號');
            $table->string('name', 30)->comment('姓名');
            $table->string('phone', 20)->comment('電話');

            $table->unique(['order_id', 'type']);
        });

        Schema::create('ord_order_status', function (Blueprint $table) {
            $table->id();
            $table->string('title', 15)->comment('名稱');
            $table->string('content', 40)->comment('解說')->nullable();
            $table->string('style', 20)->comment('樣式')->nullable();
            $table->string('code', 15)->comment('代碼');

            $table->unique(['code']);
        });

        Schema::create('ord_order_flow', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->comment('訂單id');
            $table->string('status', 15)->comment('狀態名稱');
            $table->string('status_code', 15)->comment('代碼');
            $table->timestamps();
        });

        Schema::create('ord_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('order_type', 10)->comment('訂單類別:main,sub,item');
            $table->integer('order_id')->comment('訂單類別id');
            $table->integer('sort')->default(0)->comment('訂單類別id');

            $table->string('title')->nullable()->comment('名稱');
            $table->string('sn')->nullable()->comment('序號');
            $table->string('category_title', 30)->comment('類別名稱:全館,優惠券');
            $table->string('category_code', 10)->comment('類別名稱代碼');

            $table->string('method_title', 30)->comment('優惠方式標題');
            $table->string('method_code', 10)->comment('優惠方式code');

            $table->integer('extra_id')->nullable()->comment('特殊id');
            $table->string('extra_title', 100)->nullable()->comment('特殊項目名稱');

            $table->integer('discount_value')->nullable()->comment('折扣金額');
            $table->tinyInteger('is_grand_total')->default(0)->comment('是否累計折扣');

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
        Schema::dropIfExists('ord_order_status');
        Schema::dropIfExists('ord_order_flow');
        Schema::dropIfExists('ord_discounts');

    }
}
