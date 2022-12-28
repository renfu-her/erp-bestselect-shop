<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBackStatusColumnInDlvBacPapaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dlv_bac_papa', function (Blueprint $table) {
            $table->after('memo', function ($tb) {
                $tb->string('back_status', 20)->nullable()->comment('銷貨退回明細狀態 新增退貨 / 刪除退回入庫 / 退回入庫 / 取消退回入庫');
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
        if (Schema::hasColumns('dlv_bac_papa', ['back_status'])) {
            Schema::table('dlv_bac_papa', function (Blueprint $table) {
                $table->dropColumn('back_status');
            });
        }
    }
}
