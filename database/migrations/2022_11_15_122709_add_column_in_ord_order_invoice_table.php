<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnInOrdOrderInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_order_invoice', function (Blueprint $table) {
            $table->after('qr_code_r', function ($tb) {
                $tb->string('r_invalid_status', 50)->nullable()->comment('回傳狀態(作廢)');
                $tb->string('r_invalid_msg')->nullable()->comment('回傳訊息(作廢)');
                $tb->mediumText('r_invalid_json')->nullable()->comment('回傳資料(作廢)');
                $tb->string('invalid_invoice_number', 50)->nullable()->comment('發票號碼(作廢)');
                // $tb->dateTime('invalid_at')->nullable()->comment('作廢時間');
                // $tb->string('invalid_merchant_id', 20)->nullable()->comment('商店代號(作廢)');
                // $tb->string('check_code', 100)->nullable()->comment('檢查碼');
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ord_order_invoice', function (Blueprint $table) {
            $table->dropColumn('r_invalid_status');
            $table->dropColumn('r_invalid_msg');
            $table->dropColumn('r_invalid_json');
            $table->dropColumn('invalid_invoice_number');
            // $table->dropColumn('invalid_at');
            // $table->dropColumn('invalid_merchant_id');
            // $table->dropColumn('check_code');
        });
    }
}
