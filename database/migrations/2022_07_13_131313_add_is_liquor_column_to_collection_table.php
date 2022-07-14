<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsLiquorColumnToCollectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('collection', function (Blueprint $table) {
            $table->after('is_public', function ($tb) {
                $tb->tinyInteger('is_liquor')->default('0')->comment('是否酒類 0:一般 1:酒類');
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
        if (Schema::hasColumns('collection', [
            'is_liquor',
        ])) {
            Schema::table('collection', function (Blueprint $table) {
                $table->dropColumn('is_liquor');
            });
        }
    }
}
