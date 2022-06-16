<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTextRequestColumnInDlvLogisticProjLogisticLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dlv_logistic_proj_logistic_log', function (Blueprint $table) {
            $table->string('text_request', 1000)->nullable()->default(null)->comment('上行文本')->change();
            $table->after('logistic_fk', function ($tb) {
                $tb->integer('feature')->nullable()->default(null)->comment('功能');
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
        Schema::table('dlv_logistic_proj_logistic_log', function (Blueprint $table) {
            $table->string('text_request', 255)->nullable()->default(null)->comment('上行文本')->change();
            $table->dropColumn('feature');
        });
    }
}
