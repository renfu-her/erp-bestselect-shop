<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrdTypeInCsnConsignmentItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csn_consignment_items', function (Blueprint $table) {
            $table->after('title', function ($tb) {
                $tb->string('prd_type', 2)->default('p')->comment('商品類別p=商品,c=組合包');
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
        Schema::table('csn_consignment_items', function (Blueprint $table) {
            $table->dropColumn('prd_type');
        });
    }
}
