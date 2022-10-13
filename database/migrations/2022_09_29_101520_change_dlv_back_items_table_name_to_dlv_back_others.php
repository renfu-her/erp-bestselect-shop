<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDlvBackItemsTableNameToDlvBackOthers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dlv_back_items', function (Blueprint $table) {
            $table->after('id', function ($tb) {
                $tb->unsignedBigInteger('grade_id')->comment('會計科目id');
            });
        });
        Schema::rename('dlv_back_items', 'dlv_back_others');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumns('dlv_back_items', [
            'grade_id',
        ])) {
            Schema::table('dlv_back_items', function (Blueprint $table) {
                $table->dropColumn('grade_id');
            });
        }
        Schema::rename('dlv_back_others', 'dlv_back_items');
    }
}
