<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDealerPriceToOrdItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_items', function (Blueprint $table) {
            //
            $table->after('price', function ($tb) {
                $tb->integer('dealer_price')->default(0)->comment('經銷價');
                $tb->string('note')->nullable()->comment('備註');
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
