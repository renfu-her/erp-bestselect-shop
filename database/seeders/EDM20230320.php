<?php

namespace Database\Seeders;

use App\Enums\Customer\Newsletter;
use App\Mail\EDM\EDM20230317;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Mail;

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
        $customer = Customer::where('newsletter', Newsletter::subscribe()->value)
            ->get();
        echo "共有" . count($customer) . "筆資料";
        foreach ($customer as $item) {
            $data = [
                'hrefToGo' => 'https://bit.ly/3ZVwtue',
                'image' => "https://images-besttour.cdn.hinet.net/product_intro/imgs/137/5nJefdygvssWLG6eLzKFpcSk9BRQOs8FE1gC8qLP.webp"
            ];
            Mail::to($item->email)->queue(new EDM20230317($data));
        }
        echo "發送完畢";
    }
}
