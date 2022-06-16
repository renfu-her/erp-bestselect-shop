<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrdSaleChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prd_sale_channels', function (Blueprint $table) {
            $table->id()->comment('通路');
            $table->string('title', 20)->comment('通路名稱');
            $table->string('contact_person', 20)->comment('通路聯絡人');
            $table->string('contact_tel', 15)->comment('通路聯絡電話');
            $table->string('chargeman', 20)->comment('負責窗口');
            $table->tinyInteger('sales_type')->comment('銷售類型');
            $table->tinyInteger('use_coupon')->comment('鴻利點數');
            $table->tinyInteger('is_realtime')->default(0)->comment('即時與否');
            $table->tinyInteger('is_master')->default(0)->comment('折扣的基準');
            $table->string('code', 20)->nullable()->comment('代碼');
            $table->float('discount')->default(1)->comment('折扣');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prd_sale_channels');
    }
}
