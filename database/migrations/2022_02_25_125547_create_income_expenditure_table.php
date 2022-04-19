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
            $table->string('grade_type')->comment('1~4級科目的model name');
            $table->timestamps();
        });

        Schema::create('acc_currency', function (Blueprint $table) {
            $table->id()->comment('外幣');
            $table->string('name')->unique()->comment('外幣名稱');
            $table->decimal('rate')->comment('外幣匯率');
            $table->unsignedBigInteger('received_default_fk')
                    ->nullable()
                    ->unique()
                    ->comment('table收款單的會計科目預設值acc_received_default foreign key');
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
            $table->string('grade_type')->comment('1~4級會計科目的model class name, 共有App\Models\FirstGrade, SecondGrade, ThirdGrade, FourthGrade');
            $table->unsignedBigInteger('grade_id')->nullable()->comment('對應到1～4級科目table的primary key');

            $table->unsignedBigInteger('acc_currency_fk')->comment('幣別, foreign key');
            $table->foreign('acc_currency_fk')->references('id')->on('acc_currency');

            $table->timestamps();
        });

        Schema::create('acc_payable_cash', function (Blueprint $table) {
            $table->id()->comment('現金付款');
            $table->string('grade_type')->comment('1~4級會計科目的model class name, 共有App\Models\FirstGrade, SecondGrade, ThirdGrade, FourthGrade');
            $table->unsignedBigInteger('grade_id')->nullable()->comment('對應到1～4級科目table的primary key');
            $table->timestamps();
        });

        Schema::create('acc_payable_cheque', function (Blueprint $table) {
            $table->id()->comment('支票付款');
            $table->string('check_num')->comment('票號');
            $table->string('grade_type')->comment('1~4級會計科目的model class name, 共有App\Models\FirstGrade, SecondGrade, ThirdGrade, FourthGrade');
            $table->unsignedBigInteger('grade_id')->nullable()->comment('對應到1～4級科目table的primary key');
            $table->dateTime('maturity_date')->comment('到期日');
            $table->dateTime('cash_cheque_date')->comment('兌現日');
            $table->unsignedTinyInteger('cheque_status')->comment('支票的狀態： enum ChequeStatus::getValues()');
            $table->timestamps();
        });

        Schema::create('acc_payable_remit', function (Blueprint $table) {
            $table->id()->comment('匯款');
            $table->string('grade_type')->comment('1~4級會計科目的model class name, 共有App\Models\FirstGrade, SecondGrade, ThirdGrade, FourthGrade');
            $table->unsignedBigInteger('grade_id')->nullable()->comment('對應到1～4級科目table的primary key');
            $table->dateTime('remit_date')->comment('匯款日期');
            $table->timestamps();
        });

        Schema::create('acc_payable_account', function (Blueprint $table) {
            $table->id()->comment('應付帳款');
            $table->string('grade_type')->comment('1~4級會計科目的model class name, 共有App\Models\FirstGrade, SecondGrade, ThirdGrade, FourthGrade');
            $table->unsignedBigInteger('grade_id')->nullable()->comment('對應到1～4級科目table的primary key');
            $table->timestamps();
        });

        Schema::create('acc_payable_other', function (Blueprint $table) {
            $table->id()->comment('其它');
            $table->string('grade_type')->comment('1~4級會計科目的model class name, 共有App\Models\FirstGrade, SecondGrade, ThirdGrade, FourthGrade');
            $table->unsignedBigInteger('grade_id')->nullable()->comment('對應到1～4級科目table的primary key');
            $table->timestamps();
        });

        Schema::create('acc_payable', function (Blueprint $table) {
                $table->id()->comment('付款管理：1.連結不同的「付款單類型」、付款單ID
                                                        2.儲存不同付款方式的foreign id,
                                                        3.儲存不同付款方式中的共同欄位');
                $table->string('pay_order_type')->comment('付款單類型,存入model class name，例如:採購是App\Models\PayingOrder');
                $table->unsignedBigInteger('pay_order_id')->comment('不同「付款類型」對應到不同付款單table的 primary ID');

                $table->unsignedBigInteger('acc_income_type_fk')->comment('付款方式, foreign key');
                $table->foreign('acc_income_type_fk')->references('id')->on('acc_income_type');

                $table->string('payable_type')->comment('付款方式(支票、匯款、外幣、現金、應付帳款、其它)對應的model class name');
                $table->unsignedBigInteger('payable_id')->comment('對應付款方式(支票、匯款、外幣、現金、應付帳款、其它)table的primary id');

                $table->decimal('tw_price')->comment('金額(新台幣)');
//                $table->unsignedTinyInteger('payable_status')->comment('付款狀態：1未付款, 2已付 use enum PayableStatus');
                $table->dateTime('payment_date')->comment('付款日期');

                $table->unsignedBigInteger('accountant_id_fk')->comment('會計師, user_id foreign key');
                $table->foreign('accountant_id_fk')->references('id')->on('usr_users');

                $table->text('note')->nullable()->comment('備註');
                $table->timestamps();
        });

        Schema::create('acc_income_expenditure', function (Blueprint $table) {
            $table->id()->comment('收支科目設定');

            $table->unsignedBigInteger('acc_income_type_fk')->default(null)->comment('入款方式, foreign key');
            $table->foreign('acc_income_type_fk')->references('id')->on('acc_income_type');

            $table->integer('grade_id_fk')->nullable()->comment('acc_all_grades table id');

            $table->unsignedBigInteger('acc_currency_fk')->nullable()->unique()->default(null)->comment('外幣, foreign key');
            $table->foreign('acc_currency_fk')->references('id')->on('acc_currency');

            $table->timestamps();
        });

        Schema::create('acc_grade_default', function (Blueprint $table) {
            $table->id()->comment('付款單的會計科目預設值');
            $table->string('name')->comment('項目名稱，用來設計「預設會計科目」的項目，例如：商品存貨、物流費用');
            $table->unsignedBigInteger('default_grade_id')->comment('會計科目預設值，對應到acc_all_grades table的primary key');
            $table->timestamps();
        });

        Schema::create('acc_received_default', function (Blueprint $table) {
            $table->id()->comment('收款單的會計科目預設值');
            $table->string('name')->comment('項目名稱，用來設計「預設會計科目」的項目，例如：信用卡、退貨');
            $table->unsignedBigInteger('default_grade_id')
                    ->nullable()
                    ->comment('會計科目預設值，對應到acc_all_grades table的primary key');
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

        Schema::dropIfExists('acc_grade_default');
        Schema::dropIfExists('acc_received_default');
        Schema::dropIfExists('acc_payable_cash');
        Schema::dropIfExists('acc_payable_cheque');
        Schema::dropIfExists('acc_payable_remit');
        Schema::dropIfExists('acc_payable_currency');
        Schema::dropIfExists('acc_payable_account');
        Schema::dropIfExists('acc_payable_other');
        Schema::dropIfExists('acc_payable');
        Schema::dropIfExists('acc_income_type');
        Schema::dropIfExists('acc_currency');
        Schema::dropIfExists('acc_income_expenditure');
    }
}
