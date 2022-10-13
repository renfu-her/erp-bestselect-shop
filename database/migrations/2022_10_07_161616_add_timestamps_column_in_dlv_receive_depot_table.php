<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampsColumnInDlvReceiveDepotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dlv_receive_depot', function (Blueprint $table) {
            $table->after('audit_date', function ($tb) {
                $tb->timestamps();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumns('dlv_receive_depot', ['created_at'])) {
            Schema::table('dlv_receive_depot', function (Blueprint $table) {
                $table->dropColumn('created_at');
                $table->dropColumn('updated_at');
            });
        }
    }
}
