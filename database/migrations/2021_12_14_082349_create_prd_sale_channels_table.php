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
            $table->string('title')->comment('通路名稱');
            $table->string('contact_person')->comment('通路聯絡人');
            $table->string('contact_tel')->comment('通路聯絡電話');
            $table->string('chargeman')->comment('負責窗口');
            $table->tinyInteger('sales_type')->comment('銷售類型');
            $table->tinyInteger('use_coupon')->comment('喜鴻紅利點數');
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
        Schema::dropIfExists('prd_sale_channels');
    }
}
