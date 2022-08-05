<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferVoucherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_transfer_voucher', function (Blueprint $table) {
            $table->id();
            $table->string('sn')->nullable()->comment('傳票單號');
            $table->dateTime('voucher_date')->nullable()->comment('傳票日期');
            $table->decimal('debit_price', 15, 4)->nullable()->comment('借方金額');
            $table->decimal('credit_price', 15, 4)->nullable()->comment('貸方金額');
            $table->integer('company_id')->nullable()->comment('公司id');
            $table->string('audit_status', 10)->default(0)->comment('傳票審核狀態');
            $table->integer('auditor_id')->nullable()->comment('審核人員id');
            $table->dateTime('audit_date')->nullable()->comment('審核日期');
            $table->integer('accountant_id')->nullable()->comment('會計人員id');
            $table->integer('creator_id')->nullable()->comment('建立人員id');
            $table->integer('updator_id')->nullable()->comment('更新人員id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('acc_transfer_voucher_items', function (Blueprint $table) {
            $table->id();
            $table->integer('voucher_id')->comment('傳票id');
            $table->unsignedBigInteger('grade_id')->comment('會計科目id');
            $table->string('summary')->nullable()->comment('摘要');
            $table->string('memo')->nullable()->comment('備註');
            $table->string('debit_credit_code')->comment('借貸');
            $table->unsignedBigInteger('currency_id')->nullable()->comment('acc_currency id');
            $table->decimal('rate')->nullable()->default(1)->comment('匯率');
            $table->decimal('currency_price')->nullable()->comment('幣別金額');
            $table->decimal('final_price', 15, 4)->nullable()->comment('金額');
            $table->string('department')->nullable()->comment('部門');
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
        Schema::dropIfExists('acc_transfer_voucher');
        Schema::dropIfExists('acc_transfer_voucher_items');
    }
}
