<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shi_group', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('快遞物流名稱');
            $table->string('method')->comment('出貨方式');
            $table->string('note')->nullable()->comment('說明');
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
        Schema::dropIfExists('shi_group');
    }
}
