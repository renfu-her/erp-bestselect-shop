<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisCouponEventTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dis_coupon_event', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('sn')->unique();
            $table->integer('discount_id')->comment('優惠券id');
            $table->integer('qty_per_once')->default(1)->comment('單次領取張數');
            $table->integer('qty_limit')->nullable()->comment('總張數');
            $table->dateTime('start_date')->nullable()->comment('起始時間');
            $table->dateTime('end_date')->nullable()->comment('結束時間');
            $table->tinyInteger('active')->default(1)->comment('啟用與否');
            $table->tinyInteger('reuse')->default(0)->comment('重複領取');
            $table->softDeletes();
            $table->timestamps();
            
        });

        Schema::create('dis_coupon_event_log', function (Blueprint $table) {
            $table->id();
            $table->integer('event_id')->comment('活動id');
            $table->integer('customer_id')->comment('消費者');
            $table->integer('discount_id')->comment('優惠券');
            $table->integer('qty')->comment('數量');
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
        Schema::dropIfExists('dis_coupon_event');
        Schema::dropIfExists('dis_coupon_event_log');

    }
}
