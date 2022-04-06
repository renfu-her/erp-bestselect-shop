<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductDepotSelectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prd_product_depot_select', function (Blueprint $table) {
            $table->id();
            $table->integer('depot_id');
            $table->integer('product_id');
            $table->integer('product_style_id');
            $table->string('depot_product_no')->nullable()->comment('寄倉商品編號');
            $table->integer('ost_price')->default(0)->comment('原售價(官網)');
            $table->unsignedDecimal('depot_price', 12, 2)->default(0)->comment('寄倉售價');
            $table->integer('updated_users_id')->comment('最後更新者');
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
        Schema::dropIfExists('prd_product_depot_select');
    }
}
