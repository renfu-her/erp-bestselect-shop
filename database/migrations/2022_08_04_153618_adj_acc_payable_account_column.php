<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdjAccPayableAccountColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('acc_payable_account', function (Blueprint $table) {
            $table->after('id', function ($tb) {
                $tb->string('status_code', 10)->default(0)->comment('應付帳款狀態');// 0:未付款 1:已付款
                $tb->integer('append_pay_order_id')->nullable()->comment('已付款_付款單id');
                $tb->string('sn')->nullable()->comment('已付款_付款(消帳)單號');
                $tb->decimal('amt_net', 12, 2)->default(0)->comment('已付款_付款金額');
                $tb->dateTime('payment_date')->nullable()->comment('付款日期');
            });
        });

        Schema::table('acc_payable_account', function (Blueprint $table) {
            $table->dropColumn('grade_type');
            $table->dropColumn('grade_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('acc_payable_account', function (Blueprint $table) {
            $table->after('id', function ($tb) {
                $tb->string('grade_type')->comment('1~4級會計科目的model class name, 共有App\Models\FirstGrade, SecondGrade, ThirdGrade, FourthGrade');
                $tb->unsignedBigInteger('grade_id')->nullable()->comment('對應到1～4級科目table的primary key');
            });
        });

        Schema::table('acc_payable_account', function (Blueprint $table) {
            $table->dropColumn('status_code');
            $table->dropColumn('append_pay_order_id');
            $table->dropColumn('sn');
            $table->dropColumn('amt_net');
            $table->dropColumn('payment_date');
        });
    }
}
