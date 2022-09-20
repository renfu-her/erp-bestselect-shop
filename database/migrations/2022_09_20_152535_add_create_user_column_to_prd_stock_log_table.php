<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreateUserColumnToPrdStockLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prd_stock_log', function (Blueprint $table) {
            $table->after('note', function ($tb) {
                $tb->unsignedBigInteger('create_user_id')->nullable()->comment('操作者');
                $tb->string('create_user_name', 20)->nullable()->comment('操作者名稱');
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
        Schema::table('prd_stock_log', function (Blueprint $table) {
            $table->dropColumn('create_user_id');
            $table->dropColumn('create_user_name');
        });
    }
}
