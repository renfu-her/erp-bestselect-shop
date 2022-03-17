<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogisticTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lgt_logistic', function (Blueprint $table) {
            $table->id()->comment('物流單');
            $table->unsignedBigInteger('delivery_id')->comment('出貨單id');
            $table->string('sn', 30)->comment('物流SN');
            $table->string('package_sn', 30)->nullable()->comment('物流包裹編號SN');
            $table->unsignedBigInteger('ship_group_id')->nullable()->comment('實際物流 出貨方式id 對應shi_group.id');
            $table->integer('cost')->default(0)->comment('物流成本');
            $table->string('memo')->nullable()->comment('物流備註');
            $table->dateTime('audit_date')->nullable()->comment('審核日期');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lgt_consum', function (Blueprint $table) {
            $table->id()->comment('物流商品ID');
            $table->unsignedBigInteger('logistic_id')->comment('物流單id');
            $table->unsignedBigInteger('inbound_id')->comment('入庫單ID');
            $table->string('inbound_sn', 20)->comment('入庫單SN');
            $table->unsignedBigInteger('depot_id')->comment('收貨倉庫ID');
            $table->string('depot_name', 30)->comment('收貨倉庫名稱');
            $table->unsignedBigInteger('product_style_id')->comment('耗材商品款式ID');
            $table->string('sku', 20)->comment('耗材商品sku');
            $table->string('product_title', 40)->comment('耗材商品名稱');
            $table->integer('qty')->comment('數量');
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
        Schema::dropIfExists('lgt_logistic');
        Schema::dropIfExists('lgt_consum');
    }
}
