<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddComboIdInDlvReceiveDepotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dlv_receive_depot', function (Blueprint $table) {
            $table->after('event_item_id', function ($tb) {
                $tb->unsignedBigInteger('combo_id')->nullable()->comment('組成組合包的新id');
                $tb->string('type', 10)->default('product')->comment('商品類別product=商品,combo=組合包, element=組合包內容');
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
        Schema::table('dlv_receive_depot', function (Blueprint $table) {
            $table->dropColumn('combo_id');
        });
    }
}
