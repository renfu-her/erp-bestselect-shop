<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnlineOffloneColumnInOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prd_products', function (Blueprint $table) {
            $table->after('spec_locked', function ($tb) {
                $tb->tinyInteger('online')->default(1)->nullable()->commit('線上');
                $tb->tinyInteger('offline')->default(1)->nullable()->commit('線下');
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

    }
}
