<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBackUserColumnToDlvDeliveryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dlv_delivery', function (Blueprint $table) {
            $table->after('back_memo', function ($tb) {
                $tb->integer('back_user_id')->nullable()->comment('銷貨退回者');
                $tb->string('back_user_name', 20)->nullable()->comment('銷貨退回者名稱');
                $tb->integer('back_inbound_user_id')->nullable()->comment('退貨入庫者');
                $tb->string('back_inbound_user_name', 20)->nullable()->comment('退貨入庫者名稱');
            });
        });

        Schema::table('dlv_delivery', function (Blueprint $table) {
            $table->after('back_inbound_date', function ($tb) {
                $tb->string('back_status', 20)->nullable()->comment('銷貨退回明細狀態 新增退貨 / 刪除退回入庫 / 退回入庫 / 取消退回入庫');
                $tb->dateTime('back_status_date')->nullable()->comment('退貨狀態審核日期');
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
        if (Schema::hasColumns('dlv_delivery', ['back_user_id',])) {
            Schema::table('dlv_delivery', function (Blueprint $table) {
                $table->dropColumn('back_user_id');
                $table->dropColumn('back_user_name');
                $table->dropColumn('back_inbound_user_id');
                $table->dropColumn('back_inbound_user_name');
                $table->dropColumn('back_status');
                $table->dropColumn('back_status_date');
            });
        }
    }
}
