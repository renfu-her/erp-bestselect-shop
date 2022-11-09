<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePriceAndBonusColumnTypeToDlvBackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dlv_back', function (Blueprint $table) {
            $table->decimal('price')->comment('單價售價')->change();
            $table->decimal('bonus')->comment('獎金')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dlv_back', function (Blueprint $table) {
            $table->integer('price')->comment('單價售價')->change();
            $table->integer('bonus')->comment('獎金')->change();
        });
    }
}
