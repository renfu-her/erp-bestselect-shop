<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomeExpenditureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_income_type', function (Blueprint $table) {
            $table->id()->comment('入款方式');
            $table->string('type')->comment('入款方式');
            $table->integer('grade')->comment('1~4級科目');
            $table->timestamps();
        });

        Schema::create('acc_currency', function (Blueprint $table) {
            $table->id()->comment('外幣');
            $table->string('name')->unique()->comment('外幣名稱');
            $table->decimal('rate')->comment('外幣匯率');
        });

        Schema::table('pcs_purchase_items', function (Blueprint $table) {
            $table->unsignedBigInteger('acc_currency_fk')->nullable()->default(null)->comment('幣別, foreign key');
            $table->foreign('acc_currency_fk')->references('id')->on('acc_currency');
        });

        Schema::table('pcs_paying_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('acc_currency_fk')->nullable()->default(null)->comment('幣別, foreign key');
            $table->foreign('acc_currency_fk')->references('id')->on('acc_currency');
        });

        Schema::create('acc_payable_currency', function (Blueprint $table) {
            $table->id()->comment('外幣付款');
            $table->decimal('foreign_currency')->comment('外幣付款金額');
            $table->decimal('rate')->comment('付款時的匯率');

            $table->unsignedBigInteger('acc_currency_fk')->comment('幣別, foreign key');
            $table->foreign('acc_currency_fk')->references('id')->on('acc_currency');
        });

        Schema::create('acc_payable_cheque', function (Blueprint $table) {
            $table->id()->comment('支票付款');
            $table->string('check_num')->comment('票號');
            $table->dateTime('maturity_date')->comment('到期日');
            $table->dateTime('cash_cheque_date')->comment('兌現日');
            $table->unsignedTinyInteger('cheque_status')->comment('支票的狀態： enum ChequeStatus::getValues()');
        });

        Schema::create('acc_payable_remit', function (Blueprint $table) {
            $table->id()->comment('匯款');
            $table->dateTime('remit_date')->comment('匯款日期');
        });

        Schema::create('acc_payable', function (Blueprint $table) {
            $table->id()->comment('付款管理');
            $table->tinyInteger('type')->unique()->comment('付款類型, 0:採購');

            $table->unsignedBigInteger('all_grades_id_fk')->comment('對應到table acc_all_grades第所有會計科目的 foreign key');
            $table->foreign('all_grades_id_fk')->references('id')->on('acc_all_grades');

            $table->unsignedBigInteger('acc_income_type_fk')->comment('付款方式, foreign key');
            $table->foreign('acc_income_type_fk')->references('id')->on('acc_income_type');

            $table->unsignedBigInteger('payable_id_fk')->comment('不同付款方式對應到不同table foreign key');
            $table->decimal('tw_price')->comment('金額(新台幣)');
            $table->boolean('is_final_payment')->comment('是否是「尾款」, 1:尾款 0：訂金');


                $table->unsignedTinyInteger('payable_status')->comment('付款狀態：1未付款, 2已付 use enum PayableStatus');
            $table->dateTime('payment_date')->comment('付款日期');

            $table->unsignedBigInteger('accountant_id_fk')->comment('會計師, user_id foreign key');
            $table->foreign('accountant_id_fk')->references('id')->on('usr_users');

            $table->text('note')->nullable()->comment('備註');
        });

        Schema::create('acc_income_expenditure', function (Blueprint $table) {
            $table->id()->comment('收支科目設定');

            $table->unsignedBigInteger('acc_income_type_fk')->default(null)->comment('入款方式, foreign key');
            $table->foreign('acc_income_type_fk')->references('id')->on('acc_income_type');

            $table->integer('grade_id_fk')->nullable()->comment('對應到1～4級科目table的primary key');

            $table->unsignedBigInteger('acc_currency_fk')->nullable()->unique()->default(null)->comment('外幣, foreign key');
            $table->foreign('acc_currency_fk')->references('id')->on('acc_currency');

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
        Schema::table('acc_income_expenditure', function (Blueprint $table) {
            $table->dropForeign(['acc_income_type_fk']);
            $table->dropColumn('acc_income_type_fk');

            $table->dropForeign(['acc_currency_fk']);
            $table->dropColumn('acc_currency_fk');
        });

        Schema::dropIfExists('acc_payable_cheque');

        if (Schema::hasColumns('pcs_purchase_items', ['acc_currency_fk'])) {
            Schema::table('pcs_purchase_items', function (Blueprint $table) {
                $table->dropForeign(['acc_currency_fk']);
                $table->dropColumn('acc_currency_fk');
            });
        }

        if (Schema::hasColumns('pcs_paying_orders', ['acc_currency_fk'])) {
            Schema::table('pcs_paying_orders', function (Blueprint $table) {
                $table->dropForeign(['acc_currency_fk']);
                $table->dropColumn('acc_currency_fk');
            });
        }

        if (Schema::hasColumns('acc_payable', ['all_grades_id_fk'])) {
            Schema::table('acc_payable', function (Blueprint $table) {
                $table->dropForeign(['all_grades_id_fk']);
                $table->dropColumn('all_grades_id_fk');
            });
        }

        Schema::dropIfExists('acc_payable_remit');
        Schema::dropIfExists('acc_payable_currency');
        Schema::dropIfExists('acc_payable');
        Schema::dropIfExists('acc_income_type');
        Schema::dropIfExists('acc_currency');
        Schema::dropIfExists('acc_income_expenditure');
    }
}
