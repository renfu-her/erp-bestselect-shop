<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTitleLengthToItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pcs_purchase_items', function (Blueprint $table) {
            $table->string('title', 100)->comment('商品名稱')->change();
        });
        Schema::table('ord_items', function (Blueprint $table) {
            $table->string('product_title', 100)->comment('商品名稱')->change();
        });
        Schema::table('dlv_receive_depot', function (Blueprint $table) {
            $table->string('product_title', 100)->comment('商品名稱')->change();
        });
        Schema::table('dlv_consum', function (Blueprint $table) {
            $table->string('product_title', 100)->comment('商品名稱')->change();
        });
        Schema::table('csn_consignment_items', function (Blueprint $table) {
            $table->string('title', 100)->comment('商品名稱')->change();
        });
        Schema::table('csn_order_items', function (Blueprint $table) {
            $table->string('title', 100)->comment('商品名稱')->change();
        });
        Schema::table('pcs_purchase_inbound', function (Blueprint $table) {
            $table->string('title', 100)->comment('商品名稱')->change();
        });
        Schema::table('pcs_purchase_log', function (Blueprint $table) {
            $table->string('product_title', 100)->comment('商品名稱')->change();
        });
        Schema::table('dlv_back', function (Blueprint $table) {
            $table->string('product_title', 100)->comment('商品名稱')->change();
        });
        Schema::table('pcs_import_log', function (Blueprint $table) {
            $table->string('title', 100)->comment('商品名稱')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pcs_purchase_items', function (Blueprint $table) {
            $table->string('title', 40)->comment('商品名稱')->change();
        });
        Schema::table('ord_items', function (Blueprint $table) {
            $table->string('product_title', 40)->comment('商品名稱')->change();
        });
        Schema::table('dlv_receive_depot', function (Blueprint $table) {
            $table->string('product_title', 40)->comment('商品名稱')->change();
        });
        Schema::table('dlv_consum', function (Blueprint $table) {
            $table->string('product_title', 40)->comment('商品名稱')->change();
        });
        Schema::table('csn_consignment_items', function (Blueprint $table) {
            $table->string('title', 40)->comment('商品名稱')->change();
        });
        Schema::table('csn_order_items', function (Blueprint $table) {
            $table->string('title', 40)->comment('商品名稱')->change();
        });
        Schema::table('pcs_purchase_inbound', function (Blueprint $table) {
            $table->string('title', 40)->comment('商品名稱')->change();
        });
        Schema::table('pcs_purchase_log', function (Blueprint $table) {
            $table->string('product_title', 40)->comment('商品名稱')->change();
        });
        Schema::table('dlv_back', function (Blueprint $table) {
            $table->string('product_title', 40)->comment('商品名稱')->change();
        });
        Schema::table('pcs_import_log', function (Blueprint $table) {
            $table->string('title', 40)->comment('商品名稱')->change();
        });
    }
}
