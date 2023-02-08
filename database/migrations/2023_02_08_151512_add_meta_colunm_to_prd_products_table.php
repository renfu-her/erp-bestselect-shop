<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetaColunmToPrdProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prd_products', function (Blueprint $table) {
            $table->after('slogan', function ($tb) {
                $tb->text('meta')->nullable()->comment('meta');
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
