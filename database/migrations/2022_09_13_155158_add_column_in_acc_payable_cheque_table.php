<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnInAccPayableChequeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('acc_payable_cheque', function (Blueprint $table) {
            $table->after('due_date', function ($tb) {
                $tb->integer('grade_id')->nullable()->comment('支存銀行會計科目id');
                $tb->string('grade_code')->default('11020002')->nullable()->comment('支存銀行會計科目編碼');
                $tb->string('grade_name')->default('銀行存款-合庫長春公司戶B')->nullable()->comment('支存銀行會計科目名稱');
            });

            $table->dropColumn('banks');
            $table->dropColumn('accounts');
            $table->dropColumn('drawer');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('acc_payable_cheque', function (Blueprint $table) {
            $table->dropColumn('grade_id');
            $table->dropColumn('grade_code');
            $table->dropColumn('grade_name');

            $table->after('due_date', function ($tb) {
                $tb->string('banks')->nullable()->comment('發票(付款)銀行');
                $tb->string('accounts')->nullable()->comment('付款帳號');
                $tb->string('drawer')->nullable()->comment('發票人');
            });
        });
    }
}
