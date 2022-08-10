<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecommendCollectionIdColumnToPrdProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prd_products', function (Blueprint $table) {
            $table->after('category_id', function ($tb) {
                $tb->unsignedBigInteger('recommend_collection_id')->nullable()->comment('店長推薦_群組ID');
                $tb->tinyInteger('only_show_category')->default(0)->comment('僅提供同類商品 0:否 1:是');
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
        if (Schema::hasColumns('prd_products', ['recommend_collection_id',])) {
            Schema::table('prd_products', function (Blueprint $table) {
                $table->dropColumn('recommend_collection_id');
                $table->dropColumn('only_show_category');
            });
        }
    }
}
