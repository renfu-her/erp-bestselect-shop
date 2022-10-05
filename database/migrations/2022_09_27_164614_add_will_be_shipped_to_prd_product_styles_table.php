<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWillBeShippedToPrdProductStylesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prd_product_styles', function (Blueprint $table) {
            $table->after('in_stock', function ($tb) {
                $tb->integer('will_be_shipped')->default(0)->comment('待出貨數量');
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
        Schema::table('prd_product_styles', function (Blueprint $table) {
            //
        });
    }
}
