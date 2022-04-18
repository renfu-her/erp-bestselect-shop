<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuditDataToPcsPurchaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pcs_purchase', function (Blueprint $table) {
            $table->after('close_date', function ($tb) {
                $tb->dateTime('audit_date')->nullable()->comment('審核日期');
                $tb->unsignedBigInteger('audit_user_id')->nullable()->comment('審核者ID');
                $tb->string('audit_user_name', 20)->nullable()->comment('審核者名稱');
                $tb->tinyInteger('audit_status')->default(App\Enums\Consignment\AuditStatus::unreviewed()->value)->comment('審核狀態 0:未審核 / 1:核可 / 2:否決');
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
        Schema::table('pcs_purchase', function (Blueprint $table) {
            $table->dropColumn('audit_date');
            $table->dropColumn('audit_user_id');
            $table->dropColumn('audit_user_name');
            $table->dropColumn('audit_status');
        });
    }
}
