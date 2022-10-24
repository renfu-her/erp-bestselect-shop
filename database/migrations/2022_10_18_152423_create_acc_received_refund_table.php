<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccReceivedRefundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_received_refund', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('項目名稱');
            $table->unsignedInteger('grade_id')->nullable()->comment('會計科目id');
            $table->string('grade_code', 100)->nullable()->comment('會計科目代碼');
            $table->string('grade_name', 100)->nullable()->comment('會計科目名稱');
            $table->decimal('price', 15, 4)->default(0)->comment('金額(單價)');
            $table->unsignedInteger('qty')->default(1)->comment('數量');
            $table->decimal('total_price', 15, 4)->default(0)->comment('總金額');
            $table->tinyInteger('taxation')->default(1)->comment('應稅與否');
            $table->string('summary')->nullable()->comment('摘要');
            $table->string('note')->nullable()->comment('備註');
            $table->unsignedBigInteger('source_ro_id')->nullable()->comment('來源收款單id');
            $table->string('source_ro_sn')->nullable()->comment('來源收款單編號');
            $table->unsignedBigInteger('append_po_id')->nullable()->comment('已付款_付款單id');
            $table->string('append_po_sn')->nullable()->comment('已付款_付款(消帳)單號');
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
        Schema::dropIfExists('acc_received_refund');
    }
}
