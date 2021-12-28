<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prd_stock_log', function (Blueprint $table) {
            $table->id();
            $table->integer('product_style_id')->comment('款式ID');
            $table->integer('qty')->comment('數量');
            $table->string('event')->comment('事件');
            $table->integer('event_id')->nullable()->comment('數量');
            $table->string('note')->nullable()->comment('備註');
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
        Schema::dropIfExists('prd_stock_log');
    }
}
