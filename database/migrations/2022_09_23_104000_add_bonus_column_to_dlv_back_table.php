<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBonusColumnToDlvBackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dlv_back', function (Blueprint $table) {
            $table->after('qty', function ($tb) {
                $tb->integer('bonus')->comment('獎金');
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
            'bonus',
        ])) {
            Schema::table('dlv_back', function (Blueprint $table) {
                $table->dropColumn('bonus');
            });
        }
    }
}
