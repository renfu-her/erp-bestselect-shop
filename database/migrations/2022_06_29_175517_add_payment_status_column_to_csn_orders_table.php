<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentStatusColumnToCsnOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csn_orders', function (Blueprint $table) {
            $table->string('status_code', 20)->nullable()->comment('訂單狀態代碼');
            $table->string('status', 20)->nullable()->comment('訂單狀態');

            $table->string('payment_status', 30);
            $table->string('payment_status_title', 30);
            $table->string('payment_method', 30)->default('');
            $table->string('payment_method_title', 30)->default('');
        });

        Schema::create('csn_order_flow', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->comment('訂單id');
            $table->string('status', 15)->comment('狀態名稱');
            $table->string('status_code', 15)->comment('代碼');
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
        Schema::table('csn_orders', function (Blueprint $table) {
            $table->dropColumn('status_code');
            $table->dropColumn('status');
            $table->dropColumn('payment_status');
            $table->dropColumn('payment_status_title');
            $table->dropColumn('payment_method');
            $table->dropColumn('payment_method_title');
        });
        Schema::dropIfExists('csn_order_flow');
    }
}
