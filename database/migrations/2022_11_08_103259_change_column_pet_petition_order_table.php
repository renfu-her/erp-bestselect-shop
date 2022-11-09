<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnPetPetitionOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('pet_petition_order', function (Blueprint $table) {
            $table->renameColumn('source_id', 'order_id');
            $table->renameColumn('source_sn', 'order_sn');
            $table->renameColumn('source_type', 'order_type');
        });

        Schema::table('pet_petition_order', function (Blueprint $table) {
            $table->renameColumn('petition_id', 'source_id');   
            $table->after('id', function ($tb) {
                $tb->string('source_type')->nullable()->comment('來源類型');
            });   
        });

        Schema::rename('pet_petition_order','pet_order_sn');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
