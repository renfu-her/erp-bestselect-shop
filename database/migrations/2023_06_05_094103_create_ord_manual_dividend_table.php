<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdManualDividendTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dis_manual_dividend', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');      
            $table->string('category');
            $table->string('category_title');
            $table->timestamps();
        });

        Schema::create('dis_manual_dividend_log', function (Blueprint $table) {
            $table->id();
            $table->integer('manual_dividend_id')->comment('parent_id');
            $table->string('account')->comment('帳號');
            $table->integer('dividend');
            $table->integer('status')->comment('狀態');
            $table->string('note')->comment('備註');       
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ord_manual_dividend');
        Schema::dropIfExists('dis_manual_dividend_log');

        
    }
}
