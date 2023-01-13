<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoteColumnToShiMethodTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shi_method', function (Blueprint $table) {
            //
            $table->after('method', function ($tb) {
                $tb->text('note')->nullable()->comment('備註');
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
        Schema::table('shi_method', function (Blueprint $table) {
            //
        });
    }
}
