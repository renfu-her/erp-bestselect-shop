<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePcsPurchaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pcs_purchase', function (Blueprint $table) {
            $table->id()->comment('採購id 一張採購對一家廠商');
            $table->integer('supplier_id')->comment('廠商');
            $table->integer('purchase_id')->comment('採購人員');
            $table->string('bank_cname')->comment('匯款銀行');
            $table->string('bank_code')->comment('匯款銀行代碼');
            $table->string('bank_acount')->comment('匯款戶名');
            $table->string('bank_numer')->comment('匯款帳號');
            $table->string('invoice_num')->comment('發票號碼');
            $table->tinyInteger('pay_type')->comment('0:先付(訂金) / 1:先付(一次付清) / 2:貨到付款');
            $table->string('logistic_price')->default(0)->comment('物流運費(輸入)(提示無外加運費填0)(預設0)');
            $table->dateTime('scheduled_date')->nullable()->comment('預計進貨日期');
            $table->dateTime('close_date')->nullable()->comment('結案日期');
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
        Schema::dropIfExists('pcs_purchase');
    }
}
