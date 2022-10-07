<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeAndGradeIdColumnToDlvBackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dlv_back', function (Blueprint $table) {
            $table->after('show', function ($tb) {
                $tb->tinyInteger('type')->default(0)->comment('類別 0:商品 1:物流 2:銷貨收入');
                $tb->unsignedBigInteger('grade_id')->default(null)->comment('會計科目id');
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
        if (Schema::hasColumns('dlv_back', [
            'grade_id',
        ])) {
            Schema::table('dlv_back', function (Blueprint $table) {
                $table->dropColumn('type');
                $table->dropColumn('grade_id');
            });
        }
    }
}
