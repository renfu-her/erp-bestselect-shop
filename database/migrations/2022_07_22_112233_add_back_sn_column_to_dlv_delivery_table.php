<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBackSnColumnToDlvDeliveryTable extends Migration
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
                $tb->string('back_sn')->nullable()->comment('銷貨退回單號');
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
        if (Schema::hasColumns('dlv_delivery', ['back_sn',])) {
            Schema::table('dlv_delivery', function (Blueprint $table) {
                $table->dropColumn('back_sn');
            });
        }
    }
}
