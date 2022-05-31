<?php

namespace Database\Seeders;

use App\Enums\Customer\AccountStatus;
use App\Models\Customer;
use App\Models\CustomerLogin;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CyberbizMemberSeeder extends Seeder
{
    const CYBERBIZ_MEMBER_ID = 0;
    const NAME = 1;
    const EMAIL = 2;
    const MOBILE = 3;
    const BIRTH = 4;
    const GENDER = 5;
    //剩餘紅利點數
    const COUPON = 8;
    const PHONE = 11;
    const ADDRESS = 14;
    const ORDER_COUNTS = 15;
    const TOTAL_SPENDING = 16;
    const CREATED_AT = 17;
    const LOGIN_METHOD = 18;
    const ACCOUNT_STATUS = 20;
    const LATEST_ORDER = 22;

    /**
     * 匯入Cyberbiz會員
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Factory::create();

        $jsonFileContents = file_get_contents(database_path('seeders/') . 'memberData.json');
        $memberJsonData = json_decode($jsonFileContents, true);

        foreach ($memberJsonData['data'] as $memberData) {
            //handle member address
            $address = null;
            $city_id = null;
            $region_id = null;
            $addressName = null;
            if (!is_null($memberData[self::ADDRESS])) {
               $location = explode(' ', $memberData[self::ADDRESS], 3);
               $city = $location[0];
               $city = ($city === '台東縣' ? '臺東縣' : $city);

               $region = $location[1];
               $addressName = $location[2];
               $cityQuery = DB::table('loc_addr')
                   ->whereNull('zipcode')
                   ->where('title', '=', $city)
                   ->get()
                   ->first();
               if (!is_null($cityQuery)) {
                   $city_id = $cityQuery->id;
                   $regionQuery = DB::table('loc_addr')
                       ->where('parent_id', '=', $city_id)
                       ->where('title', '=', $region)
                       ->get()
                       ->first();
                   if (!is_null($regionQuery)) {
                       $region_id = $regionQuery->id;
                       $address = $regionQuery->zipcode.
                           ' '.
                           $cityQuery->title.
                           $regionQuery->title.
                           $addressName;
                   }
               }
            }

            if (!is_null($memberData[self::EMAIL])) {
                $customerExistQuery = Customer::where('email', '=', $memberData[self::EMAIL])->get()->first();
                if ($customerExistQuery) {
                    $loginMethods = is_null($memberData[self::LOGIN_METHOD]) ? null : explode(',', $memberData[self::LOGIN_METHOD]);
                    $loginMethodQuery = CustomerLogin::where('usr_customers_id_fk', '=', $customerExistQuery->id);
                    if ($loginMethodQuery) {
                        $loginMethodQuery->delete();
                        CustomerLogin::addLoginMethod($customerExistQuery->id, $loginMethods);
                    }

                    Customer::where('email', '=', $memberData[self::EMAIL])
                        ->update([
                            'name' => $memberData[self::NAME] ?? null,
                            'phone' => $memberData[self::PHONE] ?? null,
                            'birthday' => $memberData[self::BIRTH] === 'nan' ? null : $memberData[self::BIRTH],
                            'sex' => is_null($memberData[self::GENDER]) ? null : ($memberData[self::GENDER] === '男' ? 1 : 0),
                            'acount_status' => $memberData[self::ACCOUNT_STATUS] === '帳號已啟用' ? 1 : 0,
                            'address' => $address,
                            'city_id' => $city_id,
                            'region_id' => $region_id,
                            'addr' => $addressName,
                            'created_at' => $memberData[self::CREATED_AT],
                            'order_counts' => $memberData[self::ORDER_COUNTS],
                            'total_spending' => $memberData[self::TOTAL_SPENDING],
                            'latest_order' => $memberData[self::LATEST_ORDER] === 'nan' ? null : $memberData[self::LATEST_ORDER],
                        ]);
                } else {
                    $loginMethods = is_null($memberData[self::LOGIN_METHOD]) ? null : explode(',', $memberData[self::LOGIN_METHOD]);
                    Customer::createCustomer(
                        $memberData[self::NAME] ?? null,
                        $memberData[self::EMAIL] ?? null,
                        $this->faker->password(),
                        $memberData[self::PHONE] ?? null,
                        $memberData[self::BIRTH] === 'nan' ? null : $memberData[self::BIRTH],
                        is_null($memberData[self::GENDER]) ? null : ($memberData[self::GENDER] === '男' ? 1 : 0),
                        $memberData[self::ACCOUNT_STATUS] === '帳號已啟用' ? AccountStatus::open : AccountStatus::close,
                        $address,
                        $city_id,
                        $region_id,
                        $addressName,
                        null,
                        $loginMethods,
                    );
                }
            }
        }
    }
}
