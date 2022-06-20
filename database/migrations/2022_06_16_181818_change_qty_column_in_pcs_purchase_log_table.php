<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeQtyColumnInPcsPurchaseLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pcs_purchase_log', function (Blueprint $table) {
            $table->decimal('qty')->nullable()->comment('數量')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pcs_purchase_log', function (Blueprint $table) {
            $table->integer('qty')->nullable()->comment('數量')->change();
        });
    }
}
