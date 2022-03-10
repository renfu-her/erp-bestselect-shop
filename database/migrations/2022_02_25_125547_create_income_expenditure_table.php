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

        Schema::create('acc_cheque_status', function (Blueprint $table) {
            $table->id()->comment('支票的狀態：1~5分別是付款、兌現、押票、退票、開票');
            $table->string('status', 64);
        });

        Schema::create('acc_payable_cheque', function (Blueprint $table) {
            $table->id()->comment('支票付款');
            $table->string('check_num')->comment('票號');
            $table->dateTime('maturity_date')->comment('到期日');
            $table->dateTime('cash_cheque_date')->comment('兌現日');

            $table->unsignedBigInteger('cheque_status_fk')
                ->comment('支票的狀態：1~5分別是付款、兌現、押票、退票、開票,是 acc_cheque_status的foreign key');
            $table->foreign('cheque_status_fk')->references('id')->on('acc_cheque_status');
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

        if (Schema::hasColumns('acc_payable_cheque', ['cheque_status_fk'])) {
            Schema::table('acc_payable_cheque', function (Blueprint $table) {
                $table->dropForeign(['cheque_status_fk']);
                $table->dropColumn('cheque_status_fk');
            });
        }
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

        Schema::dropIfExists('acc_cheque_status');
        Schema::dropIfExists('acc_income_type');
        Schema::dropIfExists('acc_currency');
        Schema::dropIfExists('acc_income_expenditure');
    }
}
