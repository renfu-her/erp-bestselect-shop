<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderIdColumnToOrdOrderProfitLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_order_profit_log', function (Blueprint $table) {
            //

            $table->after('profit_id', function ($tb) {
                $tb->integer('order_id')->comment('訂單id');
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
        Schema::table('ord_order_profit_log', function (Blueprint $table) {
            //
        });
    }
}
