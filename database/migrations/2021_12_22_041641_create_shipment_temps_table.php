<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentTempsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shi_temps', function (Blueprint $table) {
            $table->id();
            $table->string('temps')->comment('運送溫度');
            $table->timestamps();
        });

        Schema::table('shi_group', function (Blueprint $table) {
            $table->after('name', function ($tb) {
                $tb->unsignedBigInteger('temps_fk')->comment('運送溫度名稱,foreign key');
                $tb->foreign('temps_fk')->references('id')->on('shi_temps');
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
        Schema::table('shi_group', function (Blueprint $table) {
            $table->dropForeign('shi_group_temps_fk_foreign');
            $table->dropColumn('temps_fk');
        });
        Schema::dropIfExists('shi_temps');
    }
}
