<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameParentCustomerIdFromCustomerPrifitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usr_customer_profit', function (Blueprint $table) {
            $table->renameColumn('parent_cusotmer_id','parent_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_prifit', function (Blueprint $table) {
            //
        });
    }
}
