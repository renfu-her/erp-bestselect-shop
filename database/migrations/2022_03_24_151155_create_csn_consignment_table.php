<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsnConsignmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csn_consignment', function (Blueprint $table) {
            $table->id()->comment('寄倉單ID');
            $table->string('sn', 20)->comment('寄倉單SN');
            $table->unsignedBigInteger('send_depot_id')->comment('寄件倉庫ID');
            $table->string('send_depot_name', 30)->comment('寄件倉庫名稱');
            $table->unsignedBigInteger('receive_depot_id')->comment('收件倉庫ID');
            $table->string('receive_depot_name', 30)->comment('收件倉庫名稱');
            $table->unsignedBigInteger('ship_temp_id')->nullable()->comment('溫層id');
            $table->string('ship_temp_name', 10)->nullable()->comment('溫層');
            $table->unsignedBigInteger('ship_event_id')->nullable()->comment('物流 出貨方式id 對應shi_group.id');
            $table->string('ship_event', 30)->nullable()->comment('物流名稱');
            $table->integer('dlv_fee')->default(0)->comment('物流費用');
            $table->string('logistic_status_code', 10)->nullable()->comment('物流狀態ID');
            $table->string('logistic_status', 20)->nullable()->comment('物流狀態 檢貨中/理貨中/待配送');
            $table->unsignedBigInteger('create_user_id')->nullable()->comment('建單者ID');
            $table->string('create_user_name', 20)->nullable()->comment('建單者名稱');
            $table->dateTime('send_date')->nullable()->comment('寄倉日期');
            $table->dateTime('audit_date')->nullable()->comment('審核日期');
            $table->unsignedBigInteger('audit_user_id')->nullable()->comment('審核者ID');
            $table->string('audit_user_name', 20)->nullable()->comment('審核者名稱');
            $table->tinyInteger('audit_status')->default(0)->comment('審核狀態 0:未審核 / 1:核可 / 2:否決');
            $table->string('memo')->nullable()->comment('備註');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('csn_consignment_items', function (Blueprint $table) {
            $table->id()->comment('寄倉商品款式ID');
            $table->unsignedBigInteger('consignment_id')->comment('寄倉單ID');
            $table->unsignedBigInteger('product_style_id')->comment('商品款式ID');
            $table->string('title', 40)->comment('商品名稱');
            $table->string('sku', 20)->comment('sku');
            $table->decimal('price')->default(0)->comment('寄倉價(單價)');
            $table->integer('num')->comment('數量');
            $table->integer('arrived_num')->default(0)->comment('到貨數量');
//            $table->tinyInteger('receive_status')->default(0)->comment('入倉狀態 0:未入 / 1:正常 / 2:缺少 / 3:短缺');
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
        Schema::dropIfExists('csn_consignment');
        Schema::dropIfExists('csn_consignment_items');
    }
}
