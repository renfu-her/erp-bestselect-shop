<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountantIdInOrdReceivedOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_received_orders', function (Blueprint $table) {
            $table->after('balance_date', function ($tb) {
                $tb->unsignedBigInteger('accountant_id')->nullable()->comment('會計 user_id');
            });
        });

        Schema::table('acc_received', function (Blueprint $table) {
            $table->dropColumn('accountant_id_fk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ord_received_orders', function (Blueprint $table) {
            $table->dropColumn('accountant_id');
        });

        Schema::table('acc_received', function (Blueprint $table) {
            $table->after('review_date', function ($tb) {
                $tb->unsignedBigInteger('accountant_id_fk')->comment('會計師, user_id foreign key');
            });
        });
    }
}
