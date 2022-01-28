<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePcsPurchaseItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pcs_purchase_items', function (Blueprint $table) {
            $table->id()->comment('採購款式id');
            $table->integer('purchase_id')->comment('採購id');
            $table->integer('product_style_id')->comment('款式product_style_id 帶出款式sku碼');
            $table->string('title')->comment('商品名稱');
            $table->string('sku')->comment('sku');
            $table->string('price')->comment('單價');
            $table->integer('num')->comment('數量');
            $table->integer('arrived_num')->default(0)->comment('到貨數量');
            $table->integer('temp_id')->nullable()->comment('溫層對應id');
            $table->string('memo')->nullable()->comment('備註');
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
        Schema::dropIfExists('pcs_purchase_items');
    }
}
