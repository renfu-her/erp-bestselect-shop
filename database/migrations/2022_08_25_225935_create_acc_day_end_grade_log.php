<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccDayEndGradeLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('acc_day_end_rp_log');

        Schema::create('acc_day_end_grade_log', function (Blueprint $table) {
            $table->id();
            $table->string('day_end_id')->nullable()->comment('日結id');
            $table->dateTime('closing_date')->nullable()->comment('日結日期');
            $table->string('source_type', 100)->nullable()->comment('來源單據類別');
            $table->integer('source_id')->comment('來源單據id');
            $table->string('source_sn')->nullable()->comment('來源單據編號');
            $table->text('source_summary')->nullable()->comment('來源單據摘要');
            $table->decimal('debit_price', 15, 4)->default(0)->comment('借方金額');
            $table->decimal('credit_price', 15, 4)->default(0)->comment('貸方金額');
            $table->decimal('net_price', 15, 4)->default(0)->comment('借貸差額');
            $table->unsignedBigInteger('grade_id')->comment('會計科目id');
            $table->string('grade_code', 100)->comment('會計科目代碼');
            $table->string('grade_name', 100)->comment('會計科目名稱');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acc_day_end_grade_log');
    }
}
