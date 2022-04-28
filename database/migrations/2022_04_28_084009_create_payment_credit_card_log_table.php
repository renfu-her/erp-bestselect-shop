<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentCreditCardLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ord_payment_credit_card_log', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->comment('訂單id');
            $table->tinyInteger('status')->nullable()->comment('交易執行狀態，0:交易成功，其餘皆為失敗');
            $table->tinyInteger('errcode')->nullable()->comment('交易錯誤代碼');
            $table->string('errdesc')->nullable()->comment('交易錯誤訊息');
            $table->text('outmac')->nullable()->comment('交易結果壓碼');
            $table->string('merid')->nullable()->comment('銀行授權使用端編號');
            $table->string('authcode')->nullable()->comment('交易授權碼');
            $table->decimal('authamt', 12 , 2)->nullable()->default(0)->comment('銀行授權交易金額');
            $table->string('lidm')->nullable()->comment('訂單編號');
            $table->string('xid')->nullable()->comment('銀行授權之交易 Unquie 序號');
            $table->string('termseq')->nullable()->comment('銀行調閱序號');
            $table->string('last4digitpan')->nullable()->comment('卡號末四碼');
            $table->string('cardnumber')->nullable()->comment('隱碼卡號');
            $table->text('authresurl')->nullable()->comment('交易結束要導回的網址');
            $table->string('hostname')->nullable();
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
        Schema::dropIfExists('ord_payment_credit_card_log');
    }
}
