<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCusotmerDividendTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_cusotmer_dividend', function (Blueprint $table) {
            $table->id();
            $table->string('category')->comment('來源類型');
            $table->string('category_sn')->comment('來源sn或id');
            $table->integer('customer_id')->comment('會員id');
            $table->integer('weight')->comment('權重'); //有期限 無期限
            $table->integer('points')->comment('點數'); //有期限 無期限
            $table->string('flag')->comment('flag'); // 註銷etc
            $table->dateTime('active_sdate')->nullable()->comment('生效時間起');
            $table->dateTime('active_edate')->nullable()->comment('生效時間迄');
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
        Schema::dropIfExists('cusotmer_dividend');
    }
}
