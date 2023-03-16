<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuditDateColumnToPcsScrapItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pcs_scraps', function (Blueprint $table) {
            $table->after('audit_user_name', function ($tb) {
                $tb->dateTime('audit_date')->nullable()->comment('審核日期');
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
        if (Schema::hasColumns('pcs_scraps', [
            'audit_date',
        ])) {
            Schema::table('pcs_scraps', function (Blueprint $table) {
                $table->dropColumn('audit_date');
            });
        }
    }
}
