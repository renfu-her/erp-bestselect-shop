<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdjAccPayableChequeTableColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_note_payable_orders', function (Blueprint $table) {
            $table->id();
            $table->string('sn')->nullable()->comment('兌現單號');//BSG220808 ASG+Ymd
            $table->decimal('amt_total_net', 12, 2)->default(0)->comment('兌現金額總計');

            $table->unsignedBigInteger('net_grade_id')->comment('支票兌現會計科目');

            $table->dateTime('cashing_date')->nullable()->comment('兌現日');

            $table->integer('creator_id')->comment('建立人員id');
            $table->integer('affirmant_id')->nullable()->comment('兌現人員id');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::dropIfExists('acc_payable_cheque');

        Schema::create('acc_payable_cheque', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->comment('票號');
            $table->dateTime('due_date')->comment('到期日');

            $table->string('banks')->nullable()->comment('發票(付款)銀行');
            $table->string('accounts')->nullable()->comment('付款帳號');
            $table->string('drawer')->nullable()->comment('發票人');
            // $table->string('deposited_area_code', 100)->nullable()->comment('存入地區代碼');
            // $table->string('deposited_area', 100)->nullable()->comment('存入地區');

            $table->string('status_code', 100)->nullable()->comment('票據狀態代碼');
            $table->string('status', 100)->nullable()->comment('票據狀態');
            $table->dateTime('cashing_date')->nullable()->comment('兌現日');
            $table->dateTime('bounce_date')->nullable()->comment('退票日');

            $table->integer('note_payable_order_id')->nullable()->comment('兌現單id');
            $table->string('sn')->nullable()->comment('兌現單號');
            $table->decimal('amt_net', 12, 2)->default(0)->comment('已兌現_兌現金額');
            $table->timestamps();
        });

        Schema::create('acc_payable_cheque_log', function (Blueprint $table) {
            $table->id();
            $table->integer('cheque_id')->comment('應付票據id');
            $table->string('status_code', 100)->nullable()->comment('票據狀態代碼');
            $table->string('status', 100)->nullable()->comment('票據狀態');
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
        Schema::dropIfExists('acc_note_payable_orders');

        Schema::dropIfExists('acc_payable_cheque');

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

        Schema::dropIfExists('acc_payable_cheque_log');
    }
}
