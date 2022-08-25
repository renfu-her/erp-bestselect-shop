<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccDayEndRpLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_day_end_rp_log', function (Blueprint $table) {
            $table->id();
            $table->string('day_end_id')->nullable()->comment('日結id');
            $table->dateTime('closing_date')->nullable()->comment('日結日期');
            $table->unsignedInteger('count')->default(0)->comment('張數');
            $table->decimal('debit_price', 15, 4)->default(0)->comment('借方金額');
            $table->decimal('credit_price', 15, 4)->default(0)->comment('貸方金額');
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
        Schema::dropIfExists('acc_day_end_rp_log');
    }
}
