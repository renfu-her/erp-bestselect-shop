<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnOrdOrderProfitLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('ord_order_profit_log', function (Blueprint $table) {
            $table->dropColumn('profit_id');
            $table->after('customer_id2', function ($tb) {
                $tb->integer('order_item_id')->comment('訂單物品id');
                $tb->string('sub_order_sn')->comment('子訂單id');
                $tb->string('product_title')->nullable()->comment('產品名稱');
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
