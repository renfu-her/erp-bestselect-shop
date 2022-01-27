<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('depot', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('倉庫名稱');
//            $table->string('sn')->comment('代碼');
            $table->boolean('can_tally')->default(0)->comment('能否理貨倉');
            $table->string('sender')->comment('倉商窗口');
            $table->string('address')->comment('地址');
            $table->string('tel')->comment('電話');
            $table->integer('city_id');
            $table->integer('region_id');
            $table->string('addr');
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
        Schema::dropIfExists('depot');
    }
}
