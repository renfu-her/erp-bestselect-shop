<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrdSupplierTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prd_suppliers_account', function (Blueprint $table) {
            $table->id()->comment('結帳日 primary key');
            $table->string('account_code')->comment('結帳日代碼，1.date(幾號) 2. end(月底) 3. other(其它)');
        });

        Schema::create('prd_suppliers_shipment', function (Blueprint $table) {
            $table->id()->comment('是否配合喜鴻物流？');
            $table->string('shipment_code')->comment('1. 未洽談 2.洽談中 3. 是 4.否');
        });

        Schema::create('prd_suppliers_invoice_ship', function (Blueprint $table) {
            $table->id()->comment('發票寄送方式');
            $table->string('invoice_code')->comment('1.郵寄出紙本 2.電子檔(不再寄出紙本) 收發票EMAIL');
        });

        Schema::create('prd_suppliers_invoice_date', function (Blueprint $table) {
            $table->id()->comment('發票寄送日');
            $table->string('invoice_date_code')->comment('1. 月底前　2.次月幾日前 3.其他開放填寫');
        });

        Schema::create('prd_suppliers', function (Blueprint $table) {
            $table->id()->comment('廠商');
            $table->string('name')->comment('廠商名稱');
            $table->string('nickname')->nullable()->comment('廠商簡稱');
            $table->string('vat_no', 8)->nullable()->comment('統一編號');
            $table->unsignedInteger('postal_code')->comment('公司郵遞區號');
            $table->string('contact_address')->nullable()->comment('公司地址');
            $table->string('contact_person')->comment('訂單聯絡人');
            $table->string('job')->comment('職稱');

            $table->string('contact_tel')->comment('公司電話');
            $table->string('extension')->nullable()->comment('分機');
            $table->string('fax')->nullable()->comment('公司傳真');
            $table->string('mobile_line')->comment('手機或Line');
            $table->string('email')->nullable()->comment('電子信箱');

            $table->string('invoice_address')->nullable()->comment('發票地址');
            $table->unsignedInteger('invoice_postal_code')->nullable()->comment('發票郵遞區號');
            $table->string('invoice_recipient')->nullable()->comment('發票收件人');
            $table->string('invoice_email')->nullable()->comment('電子檔收發票的Email，null用紙本寄出');
            $table->string('invoice_phone')->nullable()->comment('發票收件人電話');
            $table->tinyInteger('invoice_date')->nullable()->comment('發票寄送日:次月幾日前');
            $table->string('invoice_date_other')->nullable()->comment('發票寄送日:其他');

            $table->unsignedBigInteger('invoice_ship_fk')->comment('發票寄送方式, foreign key');
            $table->foreign('invoice_ship_fk')->references('id')->on('prd_suppliers_invoice_ship');

            $table->unsignedBigInteger('invoice_date_fk')->comment('發票寄送日, foreign key');
            $table->foreign('invoice_date_fk')->references('id')->on('prd_suppliers_invoice_date');

            $table->string('shipping_address')->nullable()->comment('收貨地址');
            $table->unsignedInteger('shipping_postal_code')->nullable()->comment('收貨郵遞區號');
            $table->string('shipping_recipient')->nullable()->comment('收貨聯絡人');
            $table->string('shipping_phone')->nullable()->comment('收貨聯絡人電話');

            $table->unsignedBigInteger('shipping_method_fk')->comment('是否配合喜鴻物流？ foreign key');
            $table->foreign('shipping_method_fk')->references('id')->on('prd_suppliers_shipment');

            $table->dateTime('pay_date')->nullable()->comment('付款日');

            $table->unsignedBigInteger('account_fk')->comment('結帳日, foreign key');
            $table->foreign('account_fk')->references('id')->on('prd_suppliers_account');

            $table->unsignedInteger('account_date')->nullable()->comment('結帳日:幾號');
            $table->string('account_date_other')->nullable()->comment('結帳日:其它');
            $table->string('request_data')->nullable()->comment('請款資料');
            $table->string('memo')->nullable()->comment('備註');
            $table->tinyInteger('def_paytype')->comment('預設付款方式 0:現金 1:支票 2:匯款 3:外幣 4:應付帳款 5:其他');
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
        Schema::table('prd_suppliers', function (Blueprint $table) {
            $table->dropForeign(['account_fk']);
            $table->dropColumn('account_fk');
        });
        Schema::dropIfExists('prd_suppliers_account');

        Schema::table('prd_suppliers', function (Blueprint $table) {
            $table->dropForeign(['invoice_ship_fk']);
            $table->dropColumn('invoice_ship_fk');
        });
        Schema::dropIfExists('prd_suppliers_invoice_ship');

        Schema::table('prd_suppliers', function (Blueprint $table) {
            $table->dropForeign(['invoice_date_fk']);
            $table->dropColumn('invoice_date_fk');
        });
        Schema::dropIfExists('prd_suppliers_invoice_date');

        Schema::table('prd_suppliers', function (Blueprint $table) {
            $table->dropForeign(['shipping_method_fk']);
            $table->dropColumn('shipping_method_fk');
        });
        Schema::dropIfExists('prd_suppliers_shipment');

        Schema::dropIfExists('prd_suppliers');
    }
}
