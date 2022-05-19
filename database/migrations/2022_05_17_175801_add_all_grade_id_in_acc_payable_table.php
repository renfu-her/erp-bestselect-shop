<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllGradeIdInAccPayableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('acc_payable', function (Blueprint $table) {
            $table->after('payable_id', function ($tb) {
                $tb->unsignedBigInteger('all_grades_id')->comment('付款會計科目');
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
        Schema::table('acc_payable', function (Blueprint $table) {
            $table->dropColumn('all_grades_id');
        });
    }
}
