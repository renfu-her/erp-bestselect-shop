<?php

use App\Enums\Globals\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailSendRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_send_record', function (Blueprint $table) {
            $table->id();
            $table->string('email', 100)->nullable()->comment('信箱');
            $table->string('event')->comment('事件');
            $table->tinyInteger('status')->default(Status::fail()->value)->comment('匯入狀態 0:失敗 1:成功');
            $table->longText('msg')->nullable()->comment('備註');
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
        Schema::dropIfExists('mail_send_record');
    }
}
