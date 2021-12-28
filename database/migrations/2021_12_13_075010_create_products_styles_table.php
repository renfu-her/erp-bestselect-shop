<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsStylesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prd_product_styles', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->string('title')->nullable();
            $table->string('sku')->nullable()->unique()->comment('sku碼');
            $table->integer('safety_stock')->default(0)->comment('安全庫存');
            $table->integer('in_stock')->default(0)->comment('庫存');
            $table->string('type', 2)->default('p')->comment('商品類別p=商品,c=組合包');
            $table->integer('overbought')->default(0)->comment('超買設定');
            $table->integer('spec_item1_id')->nullable()->comment('所選項目');
            $table->integer('spec_item2_id')->nullable()->comment('所選項目');
            $table->integer('spec_item3_id')->nullable()->comment('所選項目');
            $table->string('spec_item1_title')->nullable()->comment('所選項目名稱');
            $table->string('spec_item2_title')->nullable()->comment('所選項目名稱');
            $table->string('spec_item3_title')->nullable()->comment('所選項目名稱');
            $table->tinyInteger('is_active')->default(1)->comment('上下架');
            $table->string('sold_out_event')->nullable()->comment('售罄狀況');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('prd_spec', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->softDeletes();
        });

        Schema::create('prd_product_spec', function (Blueprint $table) {
            $table->integer('product_id');
            $table->integer('spec_id');
            $table->integer('rank')->default(500);
        });

        Schema::create('prd_spec_items', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->integer('spec_id');
            $table->string('title');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prd_product_styles');
        Schema::dropIfExists('prd_product_spec');
        Schema::dropIfExists('prd_spec');
        Schema::dropIfExists('prd_spec_items');
    }
}
