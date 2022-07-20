<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBackDateColumnToDlvDeliveryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dlv_delivery', function (Blueprint $table) {
            $table->after('audit_user_name', function ($tb) {
                $tb->dateTime('back_date')->nullable()->comment('退貨日期');
                $tb->string('back_memo')->nullable()->comment('備註');
                $tb->dateTime('back_inbound_date')->nullable()->comment('退貨入庫日期');
            });
        });

        Schema::create('dlv_back', function (Blueprint $table) {
            $table->id()->comment('退貨商品明細');
            $table->unsignedBigInteger('delivery_id')->comment('出貨單ID');
            $table->unsignedBigInteger('event_item_id')->nullable()->comment('事件物品ID 訂單ord_items.id、寄倉csn_consignment_items.id');
            $table->integer('product_style_id')->nullable()->comment('款式ID');
            $table->string('sku', 20)->comment('商品sku');
            $table->string('product_title', 40)->comment('商品名稱');
            $table->integer('price')->comment('單價售價');
            $table->integer('origin_qty')->comment('原始數量');
            $table->integer('qty')->comment('退貨數量');
            $table->string('memo')->nullable()->comment('備註');
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
        if (Schema::hasColumns('dlv_delivery', ['back_date',])) {
            Schema::table('dlv_delivery', function (Blueprint $table) {
                $table->dropColumn('back_date');
                $table->dropColumn('back_memo');
                $table->dropColumn('back_inbound_date');
            });
        }
        Schema::dropIfExists('dlv_back');
    }
}
