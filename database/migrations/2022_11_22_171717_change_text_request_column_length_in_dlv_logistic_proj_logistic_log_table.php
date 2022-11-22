<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTextRequestColumnLengthInDlvLogisticProjLogisticLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dlv_logistic_proj_logistic_log', function (Blueprint $table) {
            $table->longText('text_request')->nullable()->default(null)->comment('上行文本')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dlv_logistic_proj_logistic_log', function (Blueprint $table) {
            $table->string('text_request', 1000)->nullable()->default(null)->comment('上行文本')->change();
        });
    }
}
