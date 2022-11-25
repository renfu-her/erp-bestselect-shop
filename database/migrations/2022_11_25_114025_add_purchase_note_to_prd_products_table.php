<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaseNoteToPrdProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prd_products', function (Blueprint $table) {
            //
            $table->after('offline', function ($tb) {
                $tb->text('purchase_note')->nullable()->comment('採購備註');
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
        Schema::table('prd_products', function (Blueprint $table) {
            //
        });
    }
}
