<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdPaymentLinePayLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ord_payment_line_pay_log', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 100)->default('ord_orders');
            $table->integer('source_id')->comment('資料表來源id');
            $table->unsignedInteger('grade_id')->nullable()->comment('會計科目id');
            $table->string('grade_code', 100)->nullable()->comment('會計科目代碼');
            $table->string('grade_name', 100)->nullable()->comment('會計科目名稱');
            $table->string('action', 30)->nullable()->comment('交易用途');
            $table->string('return_code', 10)->nullable()->comment('交易結果代碼');
            $table->text('return_message', 10)->nullable()->comment('交易結果訊息');
            $table->longText('info')->nullable()->comment('交易資訊');
            $table->string('transaction_id', 100)->nullable()->comment('交易序號');
            $table->decimal('authamt', 12, 2)->nullable()->default(0)->comment('交易授權金額');

            // $table->string('payment_access_token', 100)->nullable()->comment('可掃描代碼');
            // $table->text('payment_url_app')->nullable()->comment('交易結果訊息');
            // $table->text('payment_url_web')->nullable()->comment('交易結果訊息');
            // $table->text('pay_info')->nullable()->comment('付款資訊');
            // $table->string('method', 100)->nullable()->comment('付款方式');
            // $table->string('cardnumber')->nullable()->comment('隱碼卡號');
            // $table->text('packages')->nullable()->comment('交易資訊');// return packages

            $table->string('checkout_mode', 50)->nullable()->default('online')->comment('線上或線下');// online / offline
            $table->string('hostname_external')->nullable()->comment('外部');
            $table->string('hostname_internal')->nullable()->comment('內部');
            $table->string('os')->nullable();
            $table->string('browser')->nullable();
            $table->text('full_agent_msg')->nullable();
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
        Schema::dropIfExists('ord_payment_line_pay_log');
    }
}
