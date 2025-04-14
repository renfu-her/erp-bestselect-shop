<?php

use App\Enums\eTicket\ETicketVendor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateYoubonTable extends Migration
{
    public function up()
    {
        Schema::create('tik_types', function (Blueprint $table) {
            $table->id()->comment('商品類型');
            $table->string('name', 50)->comment('類型名稱');
            $table->string('code', 20)->unique()->comment('類型代碼');
            $table->boolean('is_active')->default(true)->comment('是否啟用');
            $table->timestamps();
        });
        // 建立商品類型
        DB::table('tik_types')->insert([
            ['name' => '一般實體商品', 'code' => 'general', 'is_active' => true],
            ['name' => '星全安電子票', 'code' => ETicketVendor::YOUBON_CODE, 'is_active' => true],
        ]);
        Schema::table('prd_products', function (Blueprint $table) {
            $table->after('purchase_note', function ($tb) {
                $tb->unsignedBigInteger('tik_type_id')->comment('商品類型ID')->nullable();
            });
        });
        // 更新現有資料商品類型
        DB::table('prd_products')->update(['tik_type_id' => DB::table('tik_types')->where('code', 'general')->value('id')]);

        Schema::table('prd_product_styles', function (Blueprint $table) {
            $table->after('sold_out_event', function ($tb) {
                $tb->string('ticket_number', 20)->default('')->comment('票券產品編號')->nullable();
            });
        });

        Schema::create('tik_youbon_api_logs', function (Blueprint $table) {
            $table->id()->comment('星全安API呼叫記錄');
            $table->unsignedBigInteger('delivery_id')->comment('出貨單id');
            $table->text('request')->nullable()->comment('請求內容');
            $table->text('response')->nullable()->comment('回應內容');
            $table->timestamps();
        });

        Schema::create('tik_youbon_orders', function (Blueprint $table) {
            $table->id()->comment('星全安訂單紀錄');
            $table->unsignedBigInteger('delivery_id')->comment('出貨單id');
            $table->string('custbillno', 20)->comment('訂單編號');
            $table->string('billno', 20)->comment('外掛借出單號、批次借出單號');
            $table->string('borrowno', 20)->nullable()->comment('正航借出單號');
            $table->date('billdate')->comment('訂單日期');
            $table->string('statcode', 10)->default(null)->nullable()->comment('狀態回覆碼');
            $table->text('weburl')->comment('網址');
            $table->timestamps();

            $table->index('delivery_id', 'idx_delivery_id');
        });

        Schema::create('tik_youbon_items', function (Blueprint $table) {
            $table->id()->comment('星全安票券紀錄');
            $table->unsignedBigInteger('delivery_id')->comment('出貨單id');
            $table->unsignedBigInteger('event_item_id')->comment('事件物品ID');
            $table->unsignedBigInteger('rcv_depot_id')->comment('收貨倉ID');
            $table->unsignedBigInteger('order_youbon_id')->comment('星全安訂單ID');
            $table->string('productnumber', 20)->comment('產品編號');
            $table->string('prodid', 40)->comment('票券編號');
            $table->string('batchid', 20)->comment('批號');
            $table->string('ordernumber', 30)->comment('票券號碼');
            $table->string('price', 12)->comment('售價');
            $table->dateTime('use_time')->nullable()->comment('使用時間');
            $table->dateTime('back_time')->nullable()->comment('退貨時間');
            $table->timestamps();

            $table->index('delivery_id', 'idx_delivery_id');
            $table->index('event_item_id', 'idx_event_item_id');
            $table->index('rcv_depot_id', 'idx_rcv_depot_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tik_types');
        if (Schema::hasColumn('prd_products', 'tik_type_id')) {
            Schema::table('prd_products', function (Blueprint $table) {
                $table->dropColumn(['tik_type_id']);
            });
        }
        if (Schema::hasColumn('prd_product_styles', 'ticket_number')) {
            Schema::table('prd_product_styles', function (Blueprint $table) {
                $table->dropColumn('ticket_number');
            });
        }
        Schema::dropIfExists('tik_youbon_api_logs');
        Schema::dropIfExists('tik_youbon_orders');
        Schema::dropIfExists('tik_youbon_items');
    }
}
