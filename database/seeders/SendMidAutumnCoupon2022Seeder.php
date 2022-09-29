<?php

namespace Database\Seeders;

use App\Mail\MidAutumnCoupon2022;
use App\Models\Customer;
use App\Models\Discount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Mail;

class SendMidAutumnCoupon2022Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $users = [];
        $users[] = json_decode('[
            {
              "Email": "chensulan@hotmail.com",
              "qty": 2
            },
            {
              "Email": "emma711104@icloud.com",
              "qty": 2
            },
            {
              "Email": "stella7192@hotmail.com",
              "qty": 2
            },
            {
              "Email": "may_place@yahoo.com.tw",
              "qty": 2
            },
            {
              "Email": "kuolingling@hotmail.com",
              "qty": 2
            },
            {
              "Email": "stella7192@hotmail.com",
              "qty": 2
            },
            {
              "Email": "stella7192@hotmail.com",
              "qty": 2
            },
            {
              "Email": "sweet.wulala219@yahoo.com.tw",
              "qty": 2
            },
            {
              "Email": "evan6914@gmail.com",
              "qty": 2
            },
            {
              "Email": "hi911mo@gmail.com",
              "qty": 2
            },
            {
              "Email": "trouble1026@gmail.com",
              "qty": 2
            },
            {
              "Email": "ee088@yahoo.com.tw",
              "qty": 2
            },
            {
              "Email": "sharsqsq0311@gmail.com",
              "qty": 2
            },
            {
              "Email": "sunny71521@hotmail.com",
              "qty": 2
            },
            {
              "Email": "ee088@yahoo.com.tw",
              "qty": 2
            },
            {
              "Email": "cindyliu00112@gmail.com",
              "qty": 2
            },
            {
              "Email": "amy08292002@gmail.com",
              "qty": 2
            },
            {
              "Email": "branda1974@hotmail.com",
              "qty": 2
            },
            {
              "Email": "jouchang@yahoo.com",
              "qty": 2
            },
            {
              "Email": "e232715@hotmail.com",
              "qty": 2
            },
            {
              "Email": "hyuk98c@gmail.com",
              "qty": 2
            },
            {
              "Email": "chrischeng_58@msn.com",
              "qty": 2
            },
            {
              "Email": "inonameer@gmail.com",
              "qty": 2
            },
            {
              "Email": "love72727@hotmail.com",
              "qty": 2
            },
            {
              "Email": "love72727@hotmail.com",
              "qty": 2
            },
            {
              "Email": "love72727@hotmail.com",
              "qty": 2
            },
            {
              "Email": "gillian791122@gmail.com",
              "qty": 2
            },
            {
              "Email": "skybaby0113@hotmail.com",
              "qty": 2
            },
            {
              "Email": "tingting49@besttour.com.tw",
              "qty": 2
            },
            {
              "Email": "vivi0020@yahoo.com.tw",
              "qty": 2
            },
            {
              "Email": "rebecca_chang66@hotmail.com",
              "qty": 2
            },
            {
              "Email": "cookie7957@hotmail.com.tw",
              "qty": 2
            },
            {
              "Email": "mavsdirk4187@wilderness.tw",
              "qty": 2
            },
            {
              "Email": "fan10164@hotmail.com",
              "qty": 2
            },
            {
              "Email": "nicker@ms17.hinet.net",
              "qty": 2
            },
            {
              "Email": "lisa.kaoyi@yahoo.com.tw",
              "qty": 2
            },
            {
              "Email": "emma711104@icloud.com",
              "qty": 3
            },
            {
              "Email": "stella7192@hotmail.com",
              "qty": 3
            },
            {
              "Email": "stella7192@hotmail.com",
              "qty": 3
            },
            {
              "Email": "heidi700@hotmail.com",
              "qty": 3
            },
            {
              "Email": "d9427216@hotmail.com",
              "qty": 3
            },
            {
              "Email": "jym0312@yahoo.com.tw",
              "qty": 3
            },
            {
              "Email": "winnie99a@yahoo.com.tw",
              "qty": 3
            },
            {
              "Email": "jean31019@gmail.com",
              "qty": 3
            },
            {
              "Email": "jennifer945168@hotmail.com",
              "qty": 3
            },
            {
              "Email": "d523410@gmail.com",
              "qty": 3
            },
            {
              "Email": "bake903@gmail.com",
              "qty": 3
            },
            {
              "Email": "bestegeg@hotmail.com",
              "qty": 3
            },
            {
              "Email": "carol624579@hotmail.com",
              "qty": 3
            },
            {
              "Email": "lydia4132002@hotmail.com",
              "qty": 3
            },
            {
              "Email": "r95227203@ntu.edu.tw",
              "qty": 3
            },
            {
              "Email": "scott0102@gmail.com",
              "qty": 3
            },
            {
              "Email": "keni1006@hotmail.com",
              "qty": 3
            },
            {
              "Email": "wonder6@gamil.com.twc",
              "qty": 4
            },
            {
              "Email": "stella7192@hotmail.com",
              "qty": 4
            },
            {
              "Email": "alice1020tpe@besttour.com.tw",
              "qty": 4
            },
            {
              "Email": "andyoleole@gmail.com",
              "qty": 4
            },
            {
              "Email": "agree415@hotmail.com",
              "qty": 4
            },
            {
              "Email": "kuolingling@hotmail.com",
              "qty": 4
            },
            {
              "Email": "yyoufen0929@hotmail.com",
              "qty": 5
            },
            {
              "Email": "stella7192@hotmail.com",
              "qty": 5
            },
            {
              "Email": "future273543@hotmail.com",
              "qty": 5
            },
            {
              "Email": "future273543@hotmail.com",
              "qty": 5
            },
            {
              "Email": "mn3235@gmail.com",
              "qty": 5
            },
            {
              "Email": "chiaying0321lee@gmail.com",
              "qty": 5
            },
            {
              "Email": "ling@yuchen-cpa.com.tw",
              "qty": 6
            },
            {
              "Email": "temp232.fwt@gmail.com",
              "qty": 7
            },
            {
              "Email": "jacyhuang_4@msn.com",
              "qty": 8
            },
            {
              "Email": "jacyhuang_4@msn.com",
              "qty": 8
            },
            {
              "Email": "love72727@hotmail.com",
              "qty": 10
            },
            {
              "Email": "zuccalin@gmail.com",
              "qty": 10
            },
            {
              "Email": "d523410@gmail.com",
              "qty": 12
            },
            {
              "Email": "andyoleole@gmail.com",
              "qty": 15
            }
          ]');

        $users[] = [
            (Object) ['Email' =>
                "belle1126@mail2000.com.tw",
                "qty" => 68],
        ];

        $_coupon = Discount::where('title', '中秋活動滿額回饋')->get()->first();

        if (!$_coupon) {
            echo '沒有優惠券';
            return;
        }
        $err = [];
        $err_send = [];
        foreach ($users as $key => $user) {
            $aDate = date('2022-10-03 00:00:00');

            if ($key == 0) {
                $eDate = date('2023-01-03 23:59:59');
            } else {
                $eDate = date('2023-04-03 23:59:59');
            }

            foreach ($user as $u) {
                $customer = Customer::where('email', $u->Email)->get()->first();
                if (!$customer) {
                    $err[] = $u->Email;
                } else {
                    //發送email
                    $data = [
                        'active_sdate' => date('Y-m-d', strtotime($aDate))
                        , 'active_edate' => date('Y-m-d', strtotime($eDate))
                        , 'link_url' => env('FRONTEND_URL'),
                    ];
                    try {
                        Mail::to($u->Email)->queue(new MidAutumnCoupon2022($data));
                    }catch(\Exception $e){
                        $err_send[] = $u->Email;
                    }
                }
            }

        }
        printf('查無消費者 '. PHP_EOL);
        print_r($err);
        printf(PHP_EOL);
        printf('發信異常 '. PHP_EOL);
        print_r($err_send);
    }
}
