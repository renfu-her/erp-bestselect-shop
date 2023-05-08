<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePcsPurchaseReturnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pcs_purchase_return', function (Blueprint $table) {
            $table->id()->comment('採購退出單ID');
            $table->string('sn', 20)->comment('單號');
            $table->unsignedBigInteger('purchase_id')->comment('採購單ID');
            $table->unsignedBigInteger('user_id')->nullable()->comment('新增者');
            $table->string('user_name', 20)->nullable()->comment('新增者名稱');
            $table->unsignedBigInteger('audit_user_id')->nullable()->comment('審核者ID');
            $table->string('audit_user_name', 20)->nullable()->comment('審核者名稱');
            $table->dateTime('audit_date')->nullable()->comment('審核日期');
            $table->unsignedBigInteger('inbound_user_id')->nullable()->comment('退貨入庫者');
            $table->string('inbound_user_name', 20)->nullable()->comment('退貨入庫者名稱');
            $table->dateTime('inbound_date')->nullable()->comment('退貨入庫日期');
            $table->string('memo')->nullable()->comment('備註');
            $table->string('status', 20)->nullable()->comment('退出單狀態');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pcs_purchase_return_items', function (Blueprint $table) {
            $table->id()->comment('採購退出商品ID');
            $table->unsignedBigInteger('return_id')->comment('採購退出單ID');
            // $table->unsignedBigInteger('inbound_id')->nullable()->comment('入庫單ID');
            $table->unsignedBigInteger('purchase_item_id')->nullable()->comment('原採購品項ID');
            $table->integer('product_style_id')->nullable()->comment('款式ID');
            $table->string('sku', 20)->nullable()->comment('商品sku');
            $table->string('product_title', 100)->nullable()->comment('商品名稱');
            $table->integer('price')->nullable()->comment('單價');
            $table->integer('qty')->nullable()->comment('數量');
            $table->string('memo')->nullable()->comment('備註');
            $table->string('ro_note')->nullable()->comment('收款單品項備註');
            $table->string('po_note')->nullable()->comment('付款單品項備註');
            $table->tinyInteger('show')->default(0)->comment('是否揭示 0:否 1:是');
            $table->tinyInteger('type')->default(0)->comment('類別 0:商品 1:物流 2:銷貨收入');
            $table->unsignedBigInteger('grade_id')->comment('會計科目id');
            $table->timestamps();
            // $table->softDeletes();
        });

        Schema::create('pcs_purchase_element_return', function (Blueprint $table) {
            $table->id()->comment('退出元素ID');
            $table->unsignedBigInteger('inbound_id')->nullable()->comment('採購入庫單ID');
            $table->unsignedBigInteger('purchase_id')->nullable()->comment('採購單ID');
            $table->unsignedBigInteger('purchase_item_id')->nullable()->comment('原採購品項ID');
            $table->unsignedBigInteger('return_id')->nullable()->comment('退出列表ID');
            $table->unsignedBigInteger('return_item_id')->nullable()->comment('退出品項列表ID');
            $table->integer('qty')->comment('退出數量');
            $table->string('memo')->nullable()->comment('退出審核備註');
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
        Schema::dropIfExists('pcs_purchase_return');

        Schema::dropIfExists('pcs_purchase_return_items');

        Schema::dropIfExists('pcs_purchase_element_return');
    }
}
