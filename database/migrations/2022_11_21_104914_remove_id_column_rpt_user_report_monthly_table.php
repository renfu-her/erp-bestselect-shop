<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveIdColumnRptUserReportMonthlyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if (Schema::hasColumns('rpt_user_report_monthly', ['id'])) {
            Schema::table('rpt_user_report_monthly', function (Blueprint $table) {
                $table->dropColumn('id');
            });
        }
        if (Schema::hasColumns('rpt_organize_report_monthly', ['id'])) {
            Schema::table('rpt_organize_report_monthly', function (Blueprint $table) {
                $table->dropColumn('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
