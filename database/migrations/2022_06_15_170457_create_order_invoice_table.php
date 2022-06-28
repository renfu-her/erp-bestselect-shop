<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ord_order_invoice', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->comment('訂單id');
            $table->text('merge_order_id')->nullable()->comment('合併開立訂單id');
            $table->integer('invoice_id')->nullable();
            $table->integer('user_id')->nullable()->comment('經手人帳號id');
            $table->integer('customer_id')->nullable()->comment('消費者帳號id');
            $table->string('merchant_order_no')->nullable()->comment('自訂訂單編號');
            $table->string('status', 1)->nullable()->comment('開立發票方式');
            $table->dateTime('create_status_time')->nullable()->comment('預計開立日期');
            $table->string('category', 10)->nullable()->comment('發票種類');
            $table->string('buyer_name', 100)->nullable()->comment('買受人姓名');
            $table->string('buyer_ubn', 100)->nullable()->comment('買受人統一編號');
            $table->string('buyer_address')->nullable()->comment('買受人地址');
            $table->string('buyer_email', 100)->nullable()->comment('買受人電子信箱');
            $table->string('carrier_type', 2)->nullable()->comment('載具類別');
            $table->string('carrier_num')->nullable()->comment('載具編號');
            $table->integer('love_code')->nullable()->comment('捐贈碼');
            $table->string('print_flag', 1)->nullable()->comment('索取紙本發票');
            $table->string('kiosk_print_flag', 1)->nullable()->comment('是否開放Kiosk列印');
            $table->string('tax_type', 2)->nullable()->comment('課稅別');
            $table->decimal('tax_rate', 12, 4)->nullable()->comment('稅率');
            $table->string('customs_clearance', 1)->nullable()->comment('報關標記');
            $table->decimal('amt', 12, 2)->nullable()->comment('銷售額合計');
            $table->decimal('amt_sales', 12, 2)->nullable()->comment('應稅商品合計');
            $table->decimal('amt_zero', 12, 2)->nullable()->comment('零稅商品合計');
            $table->decimal('amt_free', 12, 2)->nullable()->comment('免稅商品合計');
            $table->decimal('tax_amt', 12, 2)->nullable()->comment('發票稅額');
            $table->decimal('total_amt', 12, 2)->nullable()->comment('發票金額');
            $table->mediumText('item_name')->nullable()->comment('商品名稱');
            $table->text('item_count')->nullable()->comment('商品數量');
            $table->text('item_unit')->nullable()->comment('商品單位');
            $table->text('item_price')->nullable()->comment('商品單價');
            $table->text('item_amt')->nullable()->comment('商品小計');
            $table->text('item_tax_type')->nullable()->comment('商品課稅別');
            $table->text('comment')->nullable()->comment('備註');

            $table->string('r_status', 50)->nullable()->comment('回傳狀態');
            $table->string('r_msg')->nullable()->comment('回傳訊息');
            $table->mediumText('r_json')->nullable()->comment('回傳資料');
            $table->string('invoice_trans_no', 50)->nullable()->comment('發票開立序號');
            $table->string('invoice_number', 50)->nullable()->comment('發票號碼');
            $table->string('random_number', 10)->nullable()->comment('發票防偽隨機碼');
            $table->string('check_code', 100)->nullable()->comment('檢查碼');
            $table->string('bar_code', 50)->nullable()->comment('二維碼');
            $table->string('qr_code_l')->nullable()->comment('QR CODE 左');
            $table->string('qr_code_r')->nullable()->comment('QR CODE 右');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('ord_orders', function (Blueprint $table) {
            $table->after('unique_id', function ($tb) {
                $tb->string('gui_number', 50)->nullable()->comment('統一編號');
                $tb->string('invoice_category', 100)->nullable()->comment('發票類型');
                $tb->string('invoice_number', 50)->nullable()->comment('發票號碼');
            });

            $table->string('payment_method', 100)->default('')->change();
            $table->string('payment_method_title', 100)->default('')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ord_order_invoice');

        Schema::table('ord_orders', function (Blueprint $table) {
            $table->dropColumn('gui_number');
            $table->dropColumn('invoice_category');
            $table->dropColumn('invoice_number');
            $table->string('payment_method', 30)->default('')->change();
            $table->string('payment_method_title', 30)->default('')->change();
        });
    }
}
