<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccRequestOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('acc_request_orders', function (Blueprint $table) {
            $table->dropColumn('qty');
            $table->dropColumn('total_price');
            $table->dropColumn('tw_dollar');
            $table->dropColumn('rate');
            $table->dropColumn('currency_id');
            $table->dropColumn('request_grade_id');
            $table->dropColumn('summary');
            $table->dropColumn('memo');
            $table->tinyInteger('taxation');

        });

        Schema::create('acc_request_order_items', function (Blueprint $table) {
            $table->id();
            $table->integer('request_order_id')->comment('請款單id');
            $table->decimal('price', 12, 2)->nullable()->comment('金額');
            $table->integer('qty')->nullable()->comment('數量');
            $table->decimal('total_price', 12, 2)->nullable()->comment('總金額');
            $table->decimal('tw_dollar')->nullable()->default(null)->comment('新台幣');
            $table->decimal('rate')->nullable()->default(null)->comment('匯率');
            $table->unsignedBigInteger('currency_id')->nullable()->comment('acc_currency id');
            $table->unsignedBigInteger('grade_id')->comment('會計科目id');
            $table->string('summary')->nullable()->comment('摘要');
            $table->string('memo')->nullable()->comment('備註');
            $table->string('ro_note')->nullable()->comment('備註');
            $table->tinyInteger('taxation')->default(1)->comment('應稅與否');
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
        Schema::dropIfExists('acc_request_order_items');

        Schema::table('acc_request_orders', function (Blueprint $table) {
            $table->after('sn', function ($tb) {
                $tb->integer('qty')->nullable()->comment('數量');
                $tb->decimal('total_price', 12, 2)->nullable()->comment('總金額');
                $tb->decimal('tw_dollar')->nullable()->default(null)->comment('新台幣');
                $tb->decimal('rate')->nullable()->default(null)->comment('匯率');
                $tb->unsignedBigInteger('currency_id')->nullable()->comment('acc_currency id');
                $tb->unsignedBigInteger('request_grade_id')->comment('請款單會計科目id');
                $tb->string('summary')->nullable()->comment('摘要');
                $tb->string('memo')->nullable()->comment('備註');
                $tb->tinyInteger('taxation')->default(1)->comment('應稅與否');
            });
        });
    }
}
