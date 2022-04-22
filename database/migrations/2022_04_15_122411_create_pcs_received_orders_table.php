<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePcsReceivedOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pcs_received_orders', function (Blueprint $table) {
            $table->id()->comment('收款單id');
            $table->integer('order_id')->comment('訂單id');
            $table->integer('usr_users_id')->nullable()->comment('承辦人，產生收款單的人id，usr_users foreign key');

            $table->string('sn')->comment('收款單號');
            $table->decimal('price')->nullable()->comment('金額');
            $table->decimal('tw_dollar')->nullable()->default(null)->comment('新台幣,暫留欄位');
            $table->decimal('rate')->nullable()->default(null)->comment('訂單當時的匯率,暫留欄位');
            $table->unsignedBigInteger('logistics_grade_id')->comment('物流會計科目id，對應到acc_all_grade的primary key');
            $table->unsignedBigInteger('product_grade_id')->comment('商品會計科目id，對應到acc_all_grade的primary key');
            $table->dateTime('receipt_date')->nullable()->comment('入帳日期');
            $table->string('invoice_number')->nullable()->comment('發票號碼');
            $table->string('summary')->nullable()->comment('摘要');
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
        Schema::dropIfExists('pcs_received_orders');
    }
}
