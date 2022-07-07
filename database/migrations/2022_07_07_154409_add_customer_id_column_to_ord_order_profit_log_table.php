<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerIdColumnToOrdOrderProfitLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_order_profit_log', function (Blueprint $table) {
            $table->after('bonus2', function ($tb) {
                $tb->integer('customer_id1')->comment('推薦人');
                $tb->integer('customer_id2')->nullable()->comment('推薦人上一代');
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
       
    }
}
