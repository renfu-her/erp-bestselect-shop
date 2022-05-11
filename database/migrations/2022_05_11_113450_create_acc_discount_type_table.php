<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccDiscountTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_discount_type', function (Blueprint $table) {
            $table->id();
            $table->string('code')->comment('會計科目code');
            $table->string('title')->comment('會計科目');
        });

        Schema::table('dis_discounts', function (Blueprint $table) {
            $table->after('method_title', function ($tb) {
                $tb->integer('acc_type_id')->nullable()->commit('會計科目');
            });
        });

        Schema::table('ord_discounts', function (Blueprint $table) {
            $table->after('order_id', function ($tb) {
                $tb->integer('sub_order_id')->nullable()->commit('子訂單id');
                $tb->integer('order_item_id')->nullable()->commit('物品項目id');
                $tb->integer('acc_type_id')->nullable()->commit('會計科目');
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
        Schema::dropIfExists('acc_discount_type');
    }
}
