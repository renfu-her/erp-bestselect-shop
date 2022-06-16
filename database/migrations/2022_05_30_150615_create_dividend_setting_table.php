<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDividendSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dis_dividend_setting', function (Blueprint $table) {
            $table->id();
            $table->integer('limit_day')->default(0)->comment('有效天數');
            $table->integer('auto_active_day')->default(15)->comment('自動發放鴻利天數');
        });

        Schema::table('ord_orders', function (Blueprint $table) {
            $table->after('note', function ($tb) {
                $tb->tinyInteger('auto_dividend')->default(1)->comment('自動發放鴻利');
                $tb->tinyInteger('allotted_dividend')->default(0)->comment('是否已發放鴻利');
                $tb->tinyInteger('dividend_lifecycle')->default(0)->comment('鴻利存活時間0為無限');

            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dis_dividend_setting');
    }
}
