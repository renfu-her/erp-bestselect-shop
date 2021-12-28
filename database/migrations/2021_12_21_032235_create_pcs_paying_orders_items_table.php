<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePcsPayingOrdersItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pcs_paying_orders', function (Blueprint $table) {
            $table->id()->comment('代墊單id 採購時使用');
            $table->integer('purchase_id')->comment('採購id');
            $table->tinyInteger('type')->nullable()->comment('代墊單類型 0:訂金 1:尾款');

            $table->string('bank_cname')->comment('匯款銀行');
            $table->string('bank_code')->comment('匯款銀行代碼');
            $table->string('bank_acount')->comment('匯款戶名');
            $table->string('bank_numer')->comment('匯款帳號');
            $table->string('logistic_price')->default(0)->comment('物流運費(輸入)(提示無外加運費填0)(預設0)');

            $table->string('sn')->comment('代墊單號');
            $table->string('price')->nullable()->comment('金額');
            $table->string('pay_date')->nullable()->comment('付款日期');
            $table->string('memo')->nullable()->comment('備註');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pcs_paying_orders');
    }
}
