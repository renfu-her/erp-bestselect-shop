<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsnOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csn_orders', function (Blueprint $table) {
            $table->id()->comment('寄倉訂購單ID');
            $table->string('sn', 20)->comment('寄倉訂購單SN');
            $table->unsignedBigInteger('depot_id')->comment('寄件倉庫ID');
            $table->string('depot_name', 30)->comment('寄件倉庫名稱');
            $table->unsignedBigInteger('create_user_id')->nullable()->comment('建單者ID');
            $table->string('create_user_name', 20)->nullable()->comment('建單者名稱');
            $table->dateTime('scheduled_date')->nullable()->comment('訂購日期');
            $table->dateTime('audit_date')->nullable()->comment('審核日期');
            $table->dateTime('close_date')->nullable()->comment('結案日期');
            $table->unsignedBigInteger('audit_user_id')->nullable()->comment('審核者ID');
            $table->string('audit_user_name', 20)->nullable()->comment('審核者名稱');
            $table->tinyInteger('audit_status')->default(App\Enums\Consignment\AuditStatus::unreviewed()->value)->comment('審核狀態 0:未審核 / 1:核可 / 2:否決');
            $table->string('memo')->nullable()->comment('備註');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('csn_order_items', function (Blueprint $table) {
            $table->id()->comment('寄倉訂購商品款式ID');
            $table->unsignedBigInteger('csnord_id')->comment('寄倉訂購單ID');
            $table->unsignedBigInteger('product_style_id')->comment('商品款式ID');
            $table->string('prd_type', 2)->default('p')->comment('商品類別p=商品,c=組合包');
            $table->string('product_title', 40)->comment('商品名稱');
            $table->string('sku', 20)->comment('sku');
            $table->decimal('price')->default(0)->comment('寄倉價(單價)');
            $table->integer('num')->comment('數量');
            $table->string('memo')->nullable()->comment('備註');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['csnord_id', 'product_style_id']); //寄倉訂購不可選重複的款式
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('csn_orders');
        Schema::dropIfExists('csn_order_items');
    }
}
