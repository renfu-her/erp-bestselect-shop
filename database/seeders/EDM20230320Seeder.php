<?php

namespace Database\Seeders;

use App\Enums\Customer\Newsletter;
use App\Helpers\IttmsUtils;
use App\Jobs\EDM20230320Job;
use App\Mail\EDM\EDM20230320;
use App\Models\Customer;
use App\Models\MailSendRecord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EDM20230320Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $asdf = Mail::to("ittmsapp@gmail.com")->send(new EDM20230320());
//        dispatch(new EDM20230320Job("ittmsapp@gmail.com"))->delay(now()->addSeconds(1 * 3));
        dd($asdf);
        echo "ok";
//        // 發郵件EDM
//        // 先檢查會員是否有訂閱
//        $customer = DB::table(app(Customer::class)->getTable(). ' as customers')
//            ->leftJoin(app(MailSendRecord::class)->getTable(). ' as mail_send_record', function ($join) {
//                $join->on('customers.email', '=', 'mail_send_record.email')
//                    ->where('mail_send_record.event', '=', EDM20230320::class );
//            })
//            ->where('customers.newsletter', Newsletter::subscribe()->value)
//            ->whereNull('mail_send_record.id')
//            ->whereNull('customers.deleted_at')
//            ->select('customers.email')
//            ->get()
//        ;
//        echo "共有.." . count($customer) . "筆資料";
//
//        for($i = 0; $i < count($customer); $i++) {
//            //每兩秒執行下一個
//            dispatch(new EDM20230320Job($customer[$i]->email))->delay(now()->addSeconds($i * 3));
//        }
//        echo "已全部寫入queue";
    }
}
