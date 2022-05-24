<?php

use Illuminate\Database\Migrations\Migration;

class RenameProductTitleToTitleInCsnOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE csn_order_items RENAME COLUMN product_title TO title');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE csn_order_items RENAME COLUMN title TO product_title');
    }
}
