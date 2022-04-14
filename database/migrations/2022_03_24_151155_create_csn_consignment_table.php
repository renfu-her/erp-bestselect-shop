<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsnConsignmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csn_consignment', function (Blueprint $table) {
            $table->id()->comment('寄倉單ID');
            $table->string('sn', 20)->comment('寄倉單SN');
            $table->unsignedBigInteger('send_depot_id')->comment('寄件倉庫ID');
            $table->string('send_depot_name', 30)->comment('寄件倉庫名稱');
            $table->unsignedBigInteger('receive_depot_id')->comment('收件倉庫ID');
            $table->string('receive_depot_name', 30)->comment('收件倉庫名稱');
            $table->unsignedBigInteger('create_user_id')->nullable()->comment('建單者ID');
            $table->string('create_user_name', 20)->nullable()->comment('建單者名稱');
            $table->dateTime('scheduled_date')->nullable()->comment('預計入庫日期');
            $table->dateTime('audit_date')->nullable()->comment('審核日期');
            $table->dateTime('close_date')->nullable()->comment('結案日期');
            $table->unsignedBigInteger('audit_user_id')->nullable()->comment('審核者ID');
            $table->string('audit_user_name', 20)->nullable()->comment('審核者名稱');
            $table->tinyInteger('audit_status')->default(App\Enums\Consignment\AuditStatus::unreviewed()->value)->comment('審核狀態 0:未審核 / 1:核可 / 2:否決');
            $table->string('memo')->nullable()->comment('備註');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('csn_consignment_items', function (Blueprint $table) {
            $table->id()->comment('寄倉商品款式ID');
            $table->unsignedBigInteger('consignment_id')->comment('寄倉單ID');
            $table->unsignedBigInteger('product_style_id')->comment('商品款式ID');
            $table->string('title', 40)->comment('商品名稱');
            $table->string('sku', 20)->comment('sku');
            $table->decimal('price')->default(0)->comment('寄倉價(單價)');
            $table->integer('num')->comment('數量');
            $table->string('memo')->nullable()->comment('備註');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['consignment_id', 'product_style_id']); //寄倉不可選重複的款式
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('csn_consignment');
        Schema::dropIfExists('csn_consignment_items');
    }
}
