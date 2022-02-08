<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentMethodTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shi_method', function (Blueprint $table) {
            $table->id();
            $table->string('method')->unique()->comment('出貨方式');
            $table->timestamps();
        });

        Schema::table('shi_group', function (Blueprint $table) {
            $table->after('name', function ($tb) {
                $tb->unsignedBigInteger('method_fk')->comment('出貨方式,foreign key');
                $tb->foreign('method_fk')->references('id')->on('shi_method');
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
            $table->dropForeign('shi_group_method_fk_foreign');
            $table->dropColumn('method_fk');
        });
        Schema::dropIfExists('shi_method');
    }
}
