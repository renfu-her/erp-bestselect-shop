<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shi_rule', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('group_id_fk')->comment('快遞物流名稱group id,foreign key');
            $table->foreign('group_id_fk')->references('id')->on('shi_group');

            $table->integer('min_price')->comment('最少消費金額');
            $table->integer('max_price')->comment('最多消費金額');
            $table->integer('dlv_fee')->comment('運費');
            $table->integer('dlv_cost')->nullable()->comment('成本');
            $table->integer('at_most')->nullable()->comment('最多件數');
            $table->string('is_above')->comment('以上,未滿');
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
        Schema::dropIfExists('shi_rule');
    }
}
