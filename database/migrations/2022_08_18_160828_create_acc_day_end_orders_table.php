<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccDayEndOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_day_end_orders', function (Blueprint $table) {
            $table->id();
            $table->dateTime('closing_date')->nullable()->comment('日結日期');
            $table->dateTime('p_date')->nullable()->comment('執行日結日期');
            $table->unsignedInteger('times')->default(1)->comment('日結次數');
            $table->unsignedInteger('count')->default(0)->comment('張數');
            // $table->decimal('amt_total_net', 12, 2)->default(0)->comment('日結金額總計');
            $table->string('status', 100)->nullable()->comment('異常狀態');
            $table->longText('remark')->nullable()->comment('備註');
            $table->integer('creator_id')->comment('建立人員id');
            $table->integer('clearinger_id')->nullable()->comment('日結人員id');
            $table->timestamps();

            $table->unique('closing_date', 'closing_date');
        });


        Schema::create('acc_day_end_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('day_end_id')->comment('日結id');
            $table->string('sn')->nullable()->comment('傳票編號');
            $table->string('source_type', 100)->nullable()->comment('來源單據類別');//
            $table->integer('source_id')->comment('資料表來源id');
            $table->string('source_sn')->nullable()->comment('來源單據編號');
            $table->decimal('d_c_net', 12, 2)->default(0)->comment('借貸差額');

            $table->timestamps();

            $table->unique('source_sn', 'source_sn');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acc_day_end_orders');

        Schema::dropIfExists('acc_day_end_items');
    }
}
