<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePcsPurchaseLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pcs_purchase_log', function (Blueprint $table) {
            $table->id()->comment('logID');
            $table->integer('purchase_id')->comment('採購ID');
            $table->integer('product_style_id')->comment('款式ID');
            $table->string('feature')->comment('功能');
            $table->integer('feature_id')->comment('功能ID');
            $table->string('event')->comment('事件');
            $table->integer('qty')->comment('數量');
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
        Schema::dropIfExists('pcs_purchase_log');
    }
}
