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
            $table->string('sku')->unique()->comment('sku碼');
            $table->integer('safety_stock')->default(0)->comment('安全庫存');
            $table->integer('in_stock')->default(0)->comment('庫存');
            $table->integer('overbought')->default(0)->comment('超買設定');
            $table->integer('spec_item1_id')->nullable()->comment('所選項目');
            $table->integer('spec_item2_id')->nullable()->comment('所選項目');
            $table->integer('spec_item3_id')->nullable()->comment('所選項目');
            $table->tinyInteger('can_modify')->default(1)->comment('是否可變更修改');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('prd_spec', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->softDeletes();
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
        Schema::dropIfExists('prd_spec');
        Schema::dropIfExists('prd_spec_items');
    }
}
