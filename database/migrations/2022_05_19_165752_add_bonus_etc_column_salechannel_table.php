<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBonusEtcColumnSalechannelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prd_sale_channels', function (Blueprint $table) {
            $table->after('discount', function ($tb) {
                $tb->integer('bonus_limit')->default(0)->comment('紅利上限');
                $tb->integer('bonus_rate')->default(0)->comment('紅利回饋比例');
                $tb->integer('event_bonus_rate')->default(0)->comment('活動紅利回饋比例');
                $tb->dateTime('event_sdate')->nullable()->comment('活動時間起');
                $tb->dateTime('event_edate')->nullable()->comment('活動時間迄');
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
        //
    }
}
