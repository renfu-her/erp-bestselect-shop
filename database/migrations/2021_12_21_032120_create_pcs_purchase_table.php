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
            $table->integer('purchase_user_id')->comment('採購人員');
            $table->dateTime('scheduled_date')->comment('預計進貨日期');

            $table->tinyInteger('pay_type')->nullable()->comment('0:先付(訂金) / 1:先付(一次付清) / 2:貨到付款');
            $table->string('invoice_num')->nullable()->comment('發票號碼');
            $table->dateTime('close_date')->nullable()->comment('結案日期');
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
        Schema::dropIfExists('pcs_purchase');
    }
}
