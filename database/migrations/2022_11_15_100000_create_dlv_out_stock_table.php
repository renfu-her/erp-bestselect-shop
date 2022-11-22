<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDlvOutStockTable extends Migration
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
                $tb->string('out_sn')->nullable()->comment('缺貨單號');
                $tb->dateTime('out_date')->nullable()->comment('缺貨日期');
                $tb->string('out_memo')->nullable()->comment('缺貨備註');
                $tb->integer('out_user_id')->nullable()->comment('缺貨者id');
                $tb->string('out_user_name', 20)->nullable()->comment('缺貨者名稱');
            });
        });

        Schema::create('dlv_out_stock', function (Blueprint $table) {
            $table->id()->comment('取消出貨id ');
            $table->unsignedBigInteger('delivery_id')->comment('出貨單ID');
            $table->unsignedBigInteger('event_item_id')->comment('事件物品ID 訂單ord_items.id、寄倉csn_consignment_items.id');
            $table->integer('product_style_id')->nullable()->comment('款式ID');
            $table->string('sku', 20)->comment('商品sku');
            $table->string('product_title', 100)->comment('商品名稱');
            $table->decimal('price')->default(0)->comment('單價');
            $table->integer('origin_qty')->comment('原始數量');
            $table->integer('qty')->comment('數量');
            $table->decimal('bonus')->comment('獎金');
            $table->string('memo')->nullable()->comment('備註');
            $table->timestamps();
            $table->tinyInteger('show')->default(0)->comment('是否揭示 0:否 1:是');
            $table->tinyInteger('type')->default(0)->comment('類別 對應Enum DlvOutStockType');
            $table->unsignedBigInteger('grade_id')->default(null)->comment('會計科目id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumns('dlv_delivery', ['out_date',])) {
            Schema::table('dlv_delivery', function (Blueprint $table) {
                $table->dropColumn('out_sn');
                $table->dropColumn('out_date');
                $table->dropColumn('out_memo');
                $table->dropColumn('out_user_id');
                $table->dropColumn('out_user_name');
            });
        }
        Schema::dropIfExists('dlv_out_stock');
    }
}
