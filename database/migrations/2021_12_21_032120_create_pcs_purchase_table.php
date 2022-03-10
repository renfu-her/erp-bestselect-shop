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
            $table->string('sn')->comment('採購單號');
            $table->integer('supplier_id')->comment('廠商');
            $table->string('supplier_name')->comment('廠商名稱');
            $table->string('supplier_nickname')->comment('廠商暱稱');
            $table->string('supplier_sn')->nullable()->comment('廠商訂單號');
            $table->integer('purchase_user_id')->comment('採購人員');
            $table->string('purchase_user_name')->comment('採購人員名稱');
            $table->dateTime('scheduled_date')->comment('預計進貨日期');
            //付款資訊
            $table->tinyInteger('pay_type')->nullable()->comment('採購付款方式 0:先付(訂金) / 1:先付(一次付清) / 2:貨到付款');

            $table->integer('logistics_price')->default(0)->comment('物流費用');
            $table->string('logistics_memo')->nullable()->comment('物流備註');
            $table->string('invoice_num')->nullable()->comment('發票號碼');
            $table->dateTime('invoice_date')->nullable()->comment('發票日期');
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
