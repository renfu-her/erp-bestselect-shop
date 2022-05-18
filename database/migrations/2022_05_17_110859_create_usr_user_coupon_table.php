<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrUserCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_customer_coupon', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->comment('會員id');
            $table->integer('discount_id')->comment('優惠券id');
            $table->integer('order_id')->nullable()->comment('訂單id');
            $table->tinyInteger('used')->default(0)->comment('是否使用過');
            $table->dateTime('used_at')->nullable();
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
        Schema::dropIfExists('usr_customer_coupon');
    }
}
