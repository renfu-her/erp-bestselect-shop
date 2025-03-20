<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTikAutoOrderErrorLogsTable extends Migration
{
    public function up()
    {
        Schema::create('tik_auto_order_error_logs', function (Blueprint $table) {
            $table->id()->comment('自動訂單錯誤記錄ID');
            $table->unsignedBigInteger('order_id')->nullable()->comment('訂單ID');
            $table->string('order_sn', 50)->nullable()->comment('訂單編號');
            $table->text('note')->comment('錯誤備註');
            $table->text('error_message')->comment('錯誤訊息');
            $table->text('error_trace')->nullable()->comment('錯誤追蹤資訊');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tik_auto_order_error_logs');
    }
}
