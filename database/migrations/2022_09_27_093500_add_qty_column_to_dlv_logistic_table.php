<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQtyColumnToDlvLogisticTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dlv_logistic', function (Blueprint $table) {
            $table->after('ship_group_id', function ($tb) {
                $tb->integer('qty')->default(1)->comment('數量');
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
        if (Schema::hasColumns('dlv_logistic', [
            'qty',
        ])) {
            Schema::table('dlv_logistic', function (Blueprint $table) {
                $table->dropColumn('qty');
            });
        }
    }
}
