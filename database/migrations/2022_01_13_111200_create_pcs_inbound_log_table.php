<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePcsInboundLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pcs_inbound_log', function (Blueprint $table) {
            $table->id()->comment('logID');
            $table->integer('inbound_id')->comment('入庫ID');
            $table->integer('qty')->comment('數量');
            $table->string('event')->comment('事件');
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
        Schema::dropIfExists('pcs_inbound_log');
    }
}
