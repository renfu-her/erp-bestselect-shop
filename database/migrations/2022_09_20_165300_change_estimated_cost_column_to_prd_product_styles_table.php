<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeEstimatedCostColumnToPrdProductStylesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prd_product_styles', function (Blueprint $table) {
            $table->decimal('estimated_cost', 12, 2)->default(0)->comment('參考成本單價')->change();
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
            $table->integer('estimated_cost')->default(0)->comment('參考成本單價')->change();
        });
    }
}
