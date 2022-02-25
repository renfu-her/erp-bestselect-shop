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
            $table->unsignedBigInteger('temp_id')->comment('溫層id');
            $table->string('temp_name', 10)->comment('溫層');
            $table->string('logistic_method', 10)->comment('物流分類 宅配/自取');
            $table->string('logistic_status_code', 20)->nullable()->comment('物流狀態代碼');
            $table->string('logistic_status', 20)->nullable()->comment('物流狀態 檢貨中/理貨中/待配送');
            $table->unsignedBigInteger('ship_depot_id')->nullable()->comment('出貨倉庫id');
            $table->string('ship_depot_name', 20)->nullable()->comment('出貨倉庫名稱');
            $table->string('memo')->nullable()->comment('備註');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dlv_receive_depot', function (Blueprint $table) {
            $table->id()->comment('收貨倉ID');
            $table->unsignedBigInteger('delivery_id')->comment('出貨單ID');
            $table->boolean('freebies')->default(0)->comment('贈品類型 0:一般 / 1:贈品');
            $table->unsignedBigInteger('inbound_id')->comment('入庫單ID');
            $table->unsignedBigInteger('depot_id')->comment('收貨倉庫ID');
            $table->string('depot_name', 30)->comment('收貨倉庫名稱');
            $table->unsignedBigInteger('product_style_id')->comment('商品款式ID');
            $table->string('sku', 20)->comment('商品sku');
            $table->string('product_title', 40)->comment('商品名稱');
            $table->integer('qty')->comment('數量');
            $table->dateTime('expiry_date')->nullable()->comment('有效期限');
            $table->boolean('is_setup')->default(0)->comment('是否成立 0:否 / 1:是');
            $table->softDeletes();
        });

        Schema::create('dlv_logistic_status', function (Blueprint $table) {
            $table->id();
            $table->string('title', 15)->comment('名稱');
            $table->string('content', 40)->comment('解說')->nullable();
            $table->string('style', 20)->comment('樣式')->nullable();
            $table->string('code', 15)->comment('代碼');

            $table->unique(['code']);
        });

        Schema::create('dlv_logistic_flow', function (Blueprint $table) {
            $table->id();
            $table->integer('delivery_id')->comment('訂單id');
            $table->string('status', 15)->comment('狀態名稱');
            $table->string('status_code', 15)->comment('代碼');
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
    }
}
