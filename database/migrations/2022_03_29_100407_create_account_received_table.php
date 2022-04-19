<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountReceivedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_received', function (Blueprint $table) {
            $table->id()->comment('收款管理：1.連結不同的「收款單類型」、收款單ID
                                                        2.儲存不同收款方式的foreign id,
                                                        3.儲存不同收款方式中的共同欄位');
            $table->string('received_type')->comment('收款單類型,，例如:訂單 App\Models\ReceivedOrder');
            $table->unsignedBigInteger('received_order_id')->comment('不同「收款類型」對應到不同收款單table的 primary ID');

            $table->string('received_method')->comment('收款方式(支票、信用卡、匯款、外幣、現金、應收帳款、其它、退還)對應的model class name');
            $table->unsignedBigInteger('received_method_id')->nullable()->comment('對應收款方式(支票、信用卡、匯款、外幣)table的primary id。註：現金、應收帳款、其它、退還，不需對應到其它table，為null');

            $table->unsignedBigInteger('all_grades_id')->comment('收款會計科目');

            $table->decimal('tw_price')->comment('金額(新台幣)');
            $table->dateTime('review_date')->nullable()->comment('入款審核日期');

            $table->unsignedBigInteger('accountant_id_fk')->comment('會計師, user_id foreign key');
            $table->foreign('accountant_id_fk')->references('id')->on('usr_users');

            $table->text('note')->nullable()->comment('備註');
            $table->timestamps();
        });

        Schema::create('acc_received_cheque', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->comment('票號');
            $table->dateTime('due_date')->comment('到期日');
            $table->timestamps();
        });

        Schema::create('acc_received_credit', function (Blueprint $table) {
            $table->id()->comment('收款方式：信用卡');
            $table->timestamps();
        });

        Schema::create('acc_received_remit', function (Blueprint $table) {
            $table->id()->comment('收款方式：匯款');
            $table->dateTime('remittance')->comment('匯款日期');
            $table->string('memo')->comment('水單末5碼、匯款人姓名');
            $table->timestamps();
        });

        Schema::create('acc_received_currency', function (Blueprint $table) {
            $table->id()->comment('收款方式：外幣');
            $table->decimal('currency')->comment('匯率');
            $table->decimal('foreign_currency')->comment('外幣金額');
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
        if (Schema::hasColumns('acc_received', ['accountant_id_fk'])) {
            Schema::table('acc_received', function (Blueprint $table) {
                $table->dropForeign(['accountant_id_fk']);
                $table->dropColumn('accountant_id_fk');
            });
        }

        Schema::dropIfExists('acc_received');
        Schema::dropIfExists('acc_received_cheque');
        Schema::dropIfExists('acc_received_credit');
        Schema::dropIfExists('acc_received_remit');
        Schema::dropIfExists('acc_received_currency');
    }
}
