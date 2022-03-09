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
            $table->id()->comment('付款單id 採購時使用');
            $table->integer('purchase_id')->comment('採購id');
            $table->integer('usr_users_id')->comment('承辦人，產生付款單的人id，usr_users foreign key');
            $table->tinyInteger('type')->comment('付款單類型 0:訂金 1:尾款');

            $table->string('sn')->comment('付款單號');
            $table->decimal('price')->nullable()->comment('金額');

            $table->dateTime('expecte_pay_date')->nullable()->comment('期望付款日期');
            $table->string('pay_date')->nullable()->comment('付款日期');
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
        Schema::dropIfExists('pcs_paying_orders');
    }
}
