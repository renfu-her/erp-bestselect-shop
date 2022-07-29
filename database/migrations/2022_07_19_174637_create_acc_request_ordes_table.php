<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccRequestOrdesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_request_orders', function (Blueprint $table) {
            $table->id();
            $table->string('sn')->nullable()->comment('請款單號');
            $table->decimal('price', 12, 2)->nullable()->comment('金額');
            $table->integer('qty')->nullable()->comment('數量');
            $table->decimal('total_price', 12, 2)->nullable()->comment('總金額');
            $table->decimal('tw_dollar')->nullable()->default(null)->comment('新台幣');
            $table->decimal('rate')->nullable()->default(null)->comment('匯率');
            $table->unsignedBigInteger('currency_id')->nullable()->comment('acc_currency id');
            $table->unsignedBigInteger('request_grade_id')->comment('請款單會計科目id');
            $table->string('summary')->nullable()->comment('摘要');
            $table->string('memo')->nullable()->comment('備註');
            $table->tinyInteger('taxation')->default(1)->comment('應稅與否');
            $table->integer('client_id')->nullable()->comment('對象id');
            $table->string('client_name')->nullable()->comment('對象名稱');
            $table->string('client_phone')->nullable()->comment('對象電話');
            $table->string('client_address')->nullable()->comment('對象地址');
            $table->integer('creator_id')->comment('建立人員id');
            $table->integer('accountant_id')->nullable()->comment('會計id');
            $table->dateTime('posting_date')->nullable()->comment('入款日期');
            $table->integer('received_order_id')->nullable()->comment('收款單id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('acc_stitute_orders', function (Blueprint $table) {
            $table->id();
            $table->string('sn')->nullable()->comment('代墊單號');
            $table->decimal('price', 12, 2)->nullable()->comment('金額');
            $table->integer('qty')->nullable()->comment('數量');
            $table->decimal('total_price', 12, 2)->nullable()->comment('總金額');
            $table->decimal('tw_dollar')->nullable()->default(null)->comment('新台幣');
            $table->decimal('rate')->nullable()->default(null)->comment('匯率');
            $table->unsignedBigInteger('currency_id')->nullable()->comment('acc_currency id');
            $table->unsignedBigInteger('stitute_grade_id')->comment('代墊單會計科目id');
            $table->string('summary')->nullable()->comment('摘要');
            $table->string('memo')->nullable()->comment('備註');
            $table->tinyInteger('taxation')->default(1)->comment('應稅與否');
            $table->integer('client_id')->nullable()->comment('對象id');
            $table->string('client_name')->nullable()->comment('對象名稱');
            $table->string('client_phone')->nullable()->comment('對象電話');
            $table->string('client_address')->nullable()->comment('對象地址');
            $table->integer('creator_id')->comment('建立人員id');
            $table->integer('accountant_id')->nullable()->comment('會計id');
            $table->dateTime('payment_date')->nullable()->comment('入款日期');
            $table->integer('pay_order_id')->nullable()->comment('付款單id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('pcs_paying_orders', function (Blueprint $table) {
            $table->after('memo', function ($tb) {
                $tb->integer('payee_id')->nullable()->comment('對象id');
                $tb->string('payee_name')->nullable()->comment('對象名稱');
                $tb->string('payee_phone')->nullable()->comment('對象電話');
                $tb->string('payee_address')->nullable()->comment('對象地址');
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
        Schema::dropIfExists('acc_request_orders');

        Schema::dropIfExists('acc_stitute_orders');

        Schema::table('pcs_paying_orders', function (Blueprint $table) {
            $table->dropColumn('payee_id');
            $table->dropColumn('payee_name');
            $table->dropColumn('payee_phone');
            $table->dropColumn('payee_address');
        });
    }
}
