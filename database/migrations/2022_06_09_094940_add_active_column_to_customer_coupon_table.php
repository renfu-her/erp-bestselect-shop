<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActiveColumnToCustomerCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usr_customer_coupon', function (Blueprint $table) {
            //
            $table->after('order_id', function ($tb) {
                $tb->integer('from_order_id')->comment('來源order');
                $tb->integer('limit_day')->comment('0為無限'); //有期限 無期限
                $tb->dateTime('active_sdate')->nullable()->comment('生效時間起');
                $tb->dateTime('active_edate')->nullable()->comment('生效時間迄');
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
