<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RenameRcodeToMcodeInOrdOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_orders', function (Blueprint $table) {
            $table->renameColumn('rcode', 'mcode');
        });
        Schema::table('ord_orders', function (Blueprint $table) {
            $table->string('mcode', 20)->nullable()->comment('mcode消費者sn')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ord_orders', function (Blueprint $table) {
            $table->renameColumn('mcode', 'rcode');
        });
        Schema::table('ord_orders', function (Blueprint $table) {
            $table->integer('rcode')->nullable()->comment('rcode消費者id')->change();
        });
    }
}
