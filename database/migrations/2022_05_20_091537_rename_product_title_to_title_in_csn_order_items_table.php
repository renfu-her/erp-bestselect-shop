<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RenameProductTitleToTitleInCsnOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csn_order_items', function (Blueprint $table) {
            $table->renameColumn('product_title', 'title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('csn_order_items', function (Blueprint $table) {
            $table->renameColumn('title', 'product_title');
        });
    }
}
