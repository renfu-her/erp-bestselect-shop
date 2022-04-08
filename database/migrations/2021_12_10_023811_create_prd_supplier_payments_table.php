<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrdSupplierPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prd_supplier_payments', function (Blueprint $table) {
            $table->id()->comment('廠商付款方式id');
            $table->integer('supplier_id')->comment('廠商id');
            $table->tinyInteger('type')->comment('付款方式 0:現金 1:支票 2:匯款 3:外幣 4:應付帳款 5:其他');
            $table->string('bank_cname')->nullable()->comment('匯款銀行');
            $table->string('bank_code')->nullable()->comment('匯款銀行代碼');
            $table->string('bank_acount')->nullable()->comment('匯款戶名');
            $table->string('bank_numer')->nullable()->comment('匯款帳號');
            $table->string('cheque_payable')->nullable()->comment('支票抬頭');
            $table->string('other')->nullable()->comment('其他');
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
        Schema::dropIfExists('prd_supplier_payments');
    }
}
