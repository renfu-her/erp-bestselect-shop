<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBackDateColumnToDlvDeliveryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dlv_delivery', function (Blueprint $table) {
            $table->after('audit_user_name', function ($tb) {
                $tb->dateTime('back_date')->nullable()->comment('退貨日期');
            });
        });
        Schema::table('dlv_receive_depot', function (Blueprint $table) {
            $table->after('back_qty', function ($tb) {
                $tb->string('back_memo')->nullable()->comment('備註');
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
        if (Schema::hasColumns('dlv_delivery', ['back_date',])) {
            Schema::table('dlv_delivery', function (Blueprint $table) {
                $table->dropColumn('back_date');
            });
        }
        if (Schema::hasColumns('dlv_receive_depot', ['back_memo',])) {
            Schema::table('dlv_receive_depot', function (Blueprint $table) {
                $table->dropColumn('back_memo');
            });
        }
    }
}
