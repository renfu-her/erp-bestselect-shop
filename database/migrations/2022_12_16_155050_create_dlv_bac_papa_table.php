<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDlvBacPapaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dlv_bac_papa', function (Blueprint $table) {
            $table->id()->comment('退貨列表ID');
            $table->string('sn', 20)->comment('退貨單號');
            $table->unsignedBigInteger('delivery_id')->comment('出貨單ID');
            $table->integer('user_id')->nullable()->comment('銷貨退回者');
            $table->string('user_name', 20)->nullable()->comment('銷貨退回者名稱');
            $table->dateTime('inbound_date')->nullable()->comment('退貨入庫日期');
            $table->integer('inbound_user_id')->nullable()->comment('退貨入庫者');
            $table->string('inbound_user_name', 20)->nullable()->comment('退貨入庫者名稱');
            $table->string('memo')->nullable()->comment('備註');
            $table->timestamps();
        });

        Schema::table('dlv_back', function (Blueprint $table) {
            $table->after('delivery_id', function ($tb) {
                $tb->unsignedBigInteger('bac_papa_id')->comment('退貨列表ID');
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
        Schema::dropIfExists('dlv_bac_papa');
        if (Schema::hasColumns('dlv_back', ['bac_papa_id'])) {
            Schema::table('dlv_back', function (Blueprint $table) {
                $table->dropColumn('bac_papa_id');
            });
        }
    }
}
