<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEventOrderColumnToDlvBackAndDlvOutStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dlv_back', function (Blueprint $table) {
            $table->after('bac_papa_id', function ($tb) {
                $tb->string('event', 30)->nullable()->comment('事件 訂單order、寄倉consignment');
                $tb->unsignedBigInteger('event_id')->nullable()->comment('訂單ID');
                $tb->unsignedBigInteger('sub_event_id')->nullable()->comment('子訂單ID');
            });
        });
        Schema::table('dlv_out_stock', function (Blueprint $table) {
            $table->after('delivery_id', function ($tb) {
                $tb->string('event', 30)->nullable()->comment('事件 訂單order、寄倉consignment');
                $tb->unsignedBigInteger('event_id')->nullable()->comment('訂單ID');
                $tb->unsignedBigInteger('sub_event_id')->nullable()->comment('子訂單ID');
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
        if (Schema::hasColumns('dlv_back', [
            'event',
            'event_id',
            'sub_event_id',
        ])) {
            Schema::table('dlv_back', function (Blueprint $table) {
                $table->dropColumn('event');
                $table->dropColumn('event_id');
                $table->dropColumn('sub_event_id');
            });
        }
        if (Schema::hasColumns('dlv_out_stock', [
            'event',
            'event_id',
            'sub_event_id',
        ])) {
            Schema::table('dlv_out_stock', function (Blueprint $table) {
                $table->dropColumn('event');
                $table->dropColumn('event_id');
                $table->dropColumn('sub_event_id');
            });
        }
    }
}
