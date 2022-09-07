<?php

use App\Enums\Consignment\AuditStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePcsInboundInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pcs_inbound_inventory', function (Blueprint $table) {
            $table->id()->comment('入庫盤點ID');
            $table->unsignedBigInteger('inbound_id')->comment('入庫單ID');
            $table->tinyInteger('status')->default(AuditStatus::unreviewed()->value)->comment('審核狀態 0:尚未審核 1:核可 2:否決 參考Enums:Consignment\AuditStatus');

            $table->unsignedBigInteger('create_user_id')->nullable()->comment('盤點者ID');
            $table->string('create_user_name', 20)->nullable()->comment('盤點者名稱');
            $table->timestamps();

            $table->foreign('inbound_id')->references('id')->on('pcs_purchase_inbound');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pcs_inbound_inventory');
    }
}
