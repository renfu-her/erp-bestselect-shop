<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdCreateOrderLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ord_create_order_log', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable()->comment('email');
            $table->integer('success')->nullable()->comment('是否成功');
            $table->text('payload')->comment('payload');
            $table->text('return_value')->nullable()->comment('return_value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ord_create_order_log');
    }
}
