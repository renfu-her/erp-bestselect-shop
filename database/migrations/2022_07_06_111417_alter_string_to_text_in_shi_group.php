<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterStringToTextInShiGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumns('shi_group', ['note'])) {
            Schema::table('shi_group', function (Blueprint $table) {
                $table->text('note')->nullable()->comment('說明')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shi_group', function (Blueprint $table) {
            //
        });
    }
}
