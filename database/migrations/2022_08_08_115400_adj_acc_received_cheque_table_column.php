<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdjAccReceivedChequeTableColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_note_receivable_orders', function (Blueprint $table) {
            $table->id();
            $table->string('sn')->nullable()->comment('兌現單號');
            $table->decimal('amt_total_net', 12, 2)->default(0)->comment('兌現金額總計');

            $table->unsignedBigInteger('net_grade_id')->comment('支票兌現會計科目');

            $table->dateTime('cashing_date')->nullable()->comment('兌現日');

            $table->integer('creator_id')->comment('建立人員id');
            $table->integer('affirmant_id')->nullable()->comment('兌現人員id');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('acc_received_cheque', function (Blueprint $table) {
            $table->after('due_date', function ($tb) {
                $tb->string('banks')->nullable()->comment('發票(付款)銀行');
                $tb->string('accounts')->nullable()->comment('付款帳號');
                $tb->string('drawer')->nullable()->comment('發票人');
                $tb->string('deposited_area_code', 100)->nullable()->comment('存入地區代碼');
                $tb->string('deposited_area', 100)->nullable()->comment('存入地區');
                $tb->string('status_code', 100)->nullable()->comment('票據狀態代碼');
                $tb->string('status', 100)->nullable()->comment('票據狀態');

                $tb->dateTime('c_n_date')->nullable()->comment('託收、次交日');
                $tb->dateTime('cashing_date')->nullable()->comment('兌現日');
                $tb->dateTime('draw_date')->nullable()->comment('抽票日');

                $tb->integer('note_receivable_order_id')->nullable()->comment('兌現單id');
                $tb->string('sn')->nullable()->comment('兌現單號');
                $tb->decimal('amt_net', 12, 2)->default(0)->comment('已兌現_兌現金額');
            });
        });

        Schema::create('acc_received_cheque_log', function (Blueprint $table) {
            $table->id();
            $table->integer('cheque_id')->comment('應收票據id');
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
        Schema::dropIfExists('acc_note_receivable_orders');

        Schema::table('acc_received_cheque', function (Blueprint $table) {
            $table->dropColumn('banks');
            $table->dropColumn('accounts');
            $table->dropColumn('drawer');
            $table->dropColumn('deposited_area_code');
            $table->dropColumn('deposited_area');
            $table->dropColumn('status_code');
            $table->dropColumn('status');
            $table->dropColumn('c_n_date');
            $table->dropColumn('cashing_date');
            $table->dropColumn('draw_date');
            $table->dropColumn('note_receivable_order_id');
            $table->dropColumn('sn');
            $table->dropColumn('amt_net');
        });

        Schema::dropIfExists('acc_received_cheque_log');
    }
}
