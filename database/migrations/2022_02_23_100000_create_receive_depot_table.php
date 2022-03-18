<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceiveDepotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dlv_delivery', function (Blueprint $table) {
            $table->id()->comment('出貨單');
            $table->string('sn', 20)->comment('出貨單號');
            $table->string('event', 30)->comment('事件 訂單/轉倉/寄倉');
            $table->unsignedBigInteger('event_id')->comment('事件ID');
            $table->string('event_sn', 20)->comment('單號');
            $table->unsignedBigInteger('temp_id')->nullable()->comment('溫層id');
            $table->string('temp_name', 10)->nullable()->comment('溫層');
            $table->string('ship_category', 30)->comment('物流代碼');
            $table->string('ship_category_name', 30)->comment('物流類別名稱');
            $table->unsignedBigInteger('ship_depot_id')->nullable()->comment('出貨倉庫id');
            $table->string('ship_depot_name', 20)->nullable()->comment('出貨倉庫名稱');
            $table->unsignedBigInteger('ship_group_id')->nullable()->comment('出貨方式id 對應shi_group.id');

            $table->string('logistic_status_code', 10)->nullable()->comment('物流狀態ID');
            $table->string('logistic_status', 20)->nullable()->comment('物流狀態 檢貨中/理貨中/待配送');
            $table->string('memo')->nullable()->comment('備註');
            $table->dateTime('audit_date')->nullable()->comment('審核日期');
            $table->unsignedBigInteger('audit_user_id')->nullable()->comment('審核者');
            $table->string('audit_user_name')->nullable()->comment('審核者名稱');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dlv_receive_depot', function (Blueprint $table) {
            $table->id()->comment('收貨倉ID');
            $table->unsignedBigInteger('delivery_id')->comment('出貨單ID');
            $table->unsignedBigInteger('event_item_id')->nullable()->comment('事件物品ID');
            $table->boolean('freebies')->default(0)->comment('贈品類型 0:一般 / 1:贈品');
            $table->unsignedBigInteger('inbound_id')->comment('入庫單ID');
            $table->string('inbound_sn', 20)->comment('入庫單SN');
            $table->unsignedBigInteger('depot_id')->comment('收貨倉庫ID');
            $table->string('depot_name', 30)->comment('收貨倉庫名稱');
            $table->unsignedBigInteger('product_style_id')->comment('商品款式ID');
            $table->string('sku', 20)->comment('商品sku');
            $table->string('product_title', 40)->comment('商品名稱');
            $table->integer('qty')->comment('數量');
            $table->dateTime('expiry_date')->nullable()->comment('有效期限');
            $table->dateTime('audit_date')->nullable()->comment('審核日期');
            $table->softDeletes();
        });

        Schema::create('dlv_logistic', function (Blueprint $table) {
            $table->id()->comment('物流單');
            $table->unsignedBigInteger('delivery_id')->comment('出貨單id');
            $table->string('sn', 30)->comment('物流SN');
            $table->string('package_sn', 30)->nullable()->comment('物流包裹編號SN');
            $table->unsignedBigInteger('ship_group_id')->nullable()->comment('實際物流 出貨方式id 對應shi_group.id');
            $table->integer('cost')->default(0)->comment('物流成本');
            $table->string('memo')->nullable()->comment('物流備註');
            $table->dateTime('audit_date')->nullable()->comment('審核日期');
            $table->unsignedBigInteger('audit_user_id')->nullable()->comment('審核者');
            $table->string('audit_user_name')->nullable()->comment('審核者名稱');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dlv_consum', function (Blueprint $table) {
            $table->id()->comment('物流耗材商品ID');
            $table->unsignedBigInteger('logistic_id')->comment('物流單id');
            $table->unsignedBigInteger('inbound_id')->comment('入庫單ID');
            $table->string('inbound_sn', 20)->comment('入庫單SN');
            $table->unsignedBigInteger('depot_id')->comment('收貨倉庫ID');
            $table->string('depot_name', 30)->comment('收貨倉庫名稱');
            $table->unsignedBigInteger('product_style_id')->comment('耗材商品款式ID');
            $table->string('sku', 20)->comment('耗材商品sku');
            $table->string('product_title', 40)->comment('耗材商品名稱');
            $table->integer('qty')->comment('數量');
            $table->timestamps();
        });

        Schema::create('dlv_logistic_flow', function (Blueprint $table) {
            $table->id();
            $table->integer('delivery_id')->comment('訂單id');
            $table->string('status', 10)->comment('狀態名稱');
            $table->string('status_code', 10)->comment('代碼');
            $table->unsignedBigInteger('user_id')->comment('新增者');
            $table->string('user_name')->comment('新增者名稱');
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
        Schema::dropIfExists('dlv_delivery');
        Schema::dropIfExists('dlv_receive_depot');
        Schema::dropIfExists('dlv_logistic');
        Schema::dropIfExists('dlv_consum');
        Schema::dropIfExists('dlv_logistic_flow');
    }
}
