<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prd_products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique()->comment('sku碼');
            $table->string('title')->comment('產品名稱');
            $table->string('url')->nullable()->comment('網址')->unique();
            $table->text('feature')->nullable()->comment('敘述');
            $table->string('slogan')->nullable()->comment('標語');
            $table->string('type', 2)->default('p')->comment('商品類別p=商品,c=組合包');
            $table->integer('category_id')->comment('類別名稱id');
            $table->text('desc')->nullable()->comment('商品內容');
            $table->integer('user_id')->comment('使用者id');
            $table->dateTime('active_sdate')->nullable()->comment('上架時間起');
            $table->dateTime('active_edate')->nullable()->comment('上架時間終');
            $table->tinyInteger('has_tax')->default(1)->comment('應稅與否');
            $table->integer('spec1_id')->nullable()->comment('規格項目');
            $table->integer('spec2_id')->nullable()->comment('規格項目');
            $table->integer('spec3_id')->nullable()->comment('規格項目');
            $table->tinyInteger('spec_locked')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('prd_product_images', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->string('url')->comment('圖片網址');
        });

        Schema::create('prd_product_supplier', function (Blueprint $table) {
            $table->integer('product_id')->comment('產品id');
            $table->integer('supplier_id')->comment('廠商id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prd_products');
        Schema::dropIfExists('prd_product_images');
        Schema::dropIfExists('prd_product_supplier');
    }
}
