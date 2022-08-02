<?php

use App\Enums\Globals\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePcsImportLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pcs_import_log', function (Blueprint $table) {
            $table->id()->comment('採購入庫LogID');
            $table->string('sn')->comment('匯入序號');
            $table->string('purchase_sn', 20)->comment('採購單號');
            $table->string('inbound_sn', 20)->nullable()->default(null)->comment('入庫單號');
            $table->tinyInteger('status')->default(Status::fail()->value)->comment('匯入狀態 0:失敗 1:成功');

            $table->string('memo')->nullable()->comment('備註');
            $table->string('sku', 20)->comment('sku');
            $table->string('title', 40)->comment('商品名稱');

            $table->integer('qty')->default(0)->comment('入庫數量');
            $table->dateTime('expiry_date')->nullable()->comment('效期');
            $table->decimal('unit_cost')->comment('庫存採購總價');
            $table->decimal('price')->comment('庫存採購總價');


            $table->integer('import_user_id')->nullable()->comment('匯入人員ID');
            $table->string('import_user_name', 20)->nullable()->comment('匯入人員名稱');
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
        Schema::dropIfExists('pcs_import_log');
    }
}
