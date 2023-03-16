<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameStatusToAuditStatusInPcsScrapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pcs_scraps', function (Blueprint $table) {
            $table->renameColumn('status', 'audit_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pcs_scraps', function (Blueprint $table) {
            $table->renameColumn('audit_status', 'status');
        });
    }
}
