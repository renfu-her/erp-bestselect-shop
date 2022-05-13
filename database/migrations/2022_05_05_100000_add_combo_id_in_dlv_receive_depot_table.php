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
                $tb->string('prd_type', 2)->default('p')->comment('商品類別p=商品,c=組合包,ce=組合包元素');
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
