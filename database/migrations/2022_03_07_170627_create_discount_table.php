<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dis_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable()->comment('名稱');
            $table->string('sn')->nullable()->comment('序號');
            $table->string('status_code')->comment('狀態碼');
            $table->string('status_title')->comment('狀態名稱');
            $table->string('category_code')->comment('類別code');
            $table->string('category_title')->comment('類別名稱:全館,優惠券');
            $table->string('method_code')->comment('優惠方式');
            $table->string('method_title')->comment('優惠方式標題');
            $table->integer('discount_value')->comment('優惠:金額趴數或是優惠券id');
            $table->tinyInteger('is_grand_total')->default(1)->comment('是否累計折扣');

            $table->integer('usage_count')->default(0)->comment('使用次數');
            $table->integer('max_usage')->nullable()->comment('限制次數');
            $table->integer('min_consume')->default(0)->comment('最低消費金額');
            $table->tinyInteger('is_global')->default(1)->comment('是否全館適用');
            $table->dateTime('start_date')->nullable()->comment('起始時間');
            $table->dateTime('end_date')->nullable()->comment('結束時間');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['sn']);
        });

        Schema::create('dis_discount_collection', function (Blueprint $table) {
            $table->id();
            $table->integer('discount_id')->comment('優惠id');
            $table->integer('collection_id')->comment('商品群組id');
        });
        /*
        Schema::create('dis_discount_customer', function (Blueprint $table) {
            $table->id();
            $table->integer('discount_id')->comment('優惠id');
            $table->integer('collection_id')->comment('商品群組id');
        });
        */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dis_discounts');
        Schema::dropIfExists('dis_discount_collection');
    }
}
