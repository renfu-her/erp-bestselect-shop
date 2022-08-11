<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccReceivedChequeLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_received_cheque_log', function (Blueprint $table) {
            $table->id();
            $table->integer('cheque_id')->comment('應收票據id');
            $table->string('status_code', 100)->nullable()->comment('票據狀態代碼');
            $table->string('status', 100)->nullable()->comment('票據狀態');
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
        Schema::dropIfExists('acc_received_cheque_log');
    }
}
