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
            $table->decimal('tw_dollar')->nullable()->default(null)->comment('新台幣,暫留欄位');
            $table->decimal('rate')->nullable()->default(null)->comment('採購當時的匯率,暫留欄位');

            $table->string('logistics_grade_type')->default('App\\\Models\\\ThirdGrade')
                ->comment('物流費用會計科目 1~4級會計科目的model class name, 共有App\Models\FirstGrade');
            $table->unsignedBigInteger('logistics_grade_id')->default(8)->comment('對應到1～4級科目table的primary key');

            $table->string('product_grade_type')->default('App\\\Models\\\ThirdGrade')
                ->comment('記錄商品會計科目 1~4級會計科目的model class name, 共有App\Models\FirstGrade, SecondGrade, ThirdGrade, FourthGrade');
            $table->unsignedBigInteger('product_grade_id')->default(4)->comment('對應到1～4級科目table的primary key');

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
