<?php

namespace Database\Seeders;

use App\Enums\Customer\Newsletter;
use App\Jobs\EDM20230320Job;
use App\Models\Customer;
use App\Models\MailSendRecord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EDM20230320 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 發郵件EDM
        // 先檢查會員是否有訂閱
        $customer = DB::table(app(Customer::class)->getTable(). ' as customers')
            ->leftJoin(app(MailSendRecord::class)->getTable(), function ($join) {
                $join->on('customers.email', '=', 'mail_send_record.email')
                    ->where('mail_send_record.event', '=', 'EDM20230320');
            })
            ->where('newsletter', Newsletter::subscribe()->value)
            ->whereNull('mail_send_record.id')
            ->get();
        echo "共有" . count($customer) . "筆資料";

        for($i = 0; $i < count($customer); $i++) {
            //每兩秒執行下一個
            dispatch(new EDM20230320Job($customer[$i]->email))->delay($i * 2);
        }
        echo "已全部寫入queue";
    }
}
