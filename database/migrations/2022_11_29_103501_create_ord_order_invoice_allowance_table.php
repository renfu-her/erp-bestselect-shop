<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdOrderInvoiceAllowanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ord_order_invoice_allowance', function (Blueprint $table) {
            $table->id();
            $table->integer('invoice_id')->nullable()->comment('折讓發票id');
            $table->integer('user_id')->nullable()->comment('經手人帳號id');
            $table->string('invoice_number', 50)->nullable()->comment('發票號碼');
            $table->string('merchant_order_no')->nullable()->comment('自定訂單編號');
            $table->string('buyer_email', 100)->nullable()->comment('折讓通知買受人電子信箱');
            $table->string('tax_type', 2)->nullable()->comment('折讓課稅別');
            $table->mediumText('item_name')->nullable()->comment('折讓商品名稱');
            $table->text('item_count')->nullable()->comment('折讓商品數量');
            $table->text('item_unit')->nullable()->comment('折讓商品單位');
            $table->text('item_price')->nullable()->comment('折讓商品單價');
            $table->text('item_amt')->nullable()->comment('折讓商品小計');
            $table->text('item_tax_type')->nullable()->comment('折讓商品課稅別');
            $table->text('item_tax_amt')->nullable()->comment('折讓商品稅額');
            $table->decimal('total_amt', 12, 2)->nullable()->comment('折讓總金額');

            $table->string('r_status', 50)->nullable()->comment('回傳狀態');
            $table->string('r_msg')->nullable()->comment('回傳訊息');
            $table->mediumText('r_json')->nullable()->comment('回傳資料');
            $table->string('merchant_id', 20)->nullable()->comment('商店代號');
            $table->string('allowance_no', 50)->nullable()->comment('折讓號碼');
            $table->decimal('remain_amt', 12, 2)->nullable()->comment('折讓後餘額');
            $table->string('check_code', 100)->nullable()->comment('檢查碼');

            $table->string('r_invalid_status', 50)->nullable()->comment('回傳狀態(作廢折讓)');
            $table->string('r_invalid_msg')->nullable()->comment('回傳訊息(作廢折讓)');
            $table->mediumText('r_invalid_json')->nullable()->comment('回傳資料(作廢折讓)');
            $table->string('invalid_allowance_no', 50)->nullable()->comment('折讓號碼(作廢折讓)');

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
        Schema::dropIfExists('ord_order_invoice_allowance');
    }
}
