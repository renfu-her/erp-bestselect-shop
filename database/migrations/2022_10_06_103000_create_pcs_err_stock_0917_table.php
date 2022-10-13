<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePcsErrStock0917Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pcs_err_stock_0917', function (Blueprint $table) {
            $table->id();
            $table->integer('no')->comment('編號');
            $table->string('type_title', 10)->comment('商品類型');
            $table->string('product_title', 100)->nullable()->comment('商品名稱');
            $table->string('spec', 100)->nullable()->comment('款式名稱');
            $table->string('sku', 20)->comment('sku');
            $table->integer('total_in_stock_num')->comment('實際庫存');
            $table->string('user_name', 100)->nullable()->comment('負責人');
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
        Schema::dropIfExists('pcs_err_stock_0917');
    }
}
