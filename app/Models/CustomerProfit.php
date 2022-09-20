<?php

namespace App\Models;

use App\Enums\Customer\ProfitStatus;
use App\Enums\Customer\ProfitType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CustomerProfit extends Model
{
    use HasFactory;
    protected $table = 'usr_customer_profit';
    protected $guarded = [];

    public static function dataList($name = null, $sn = null, $status = null)
    {
        $re = DB::table('usr_customer_profit as profit')
            ->leftJoin('usr_customers as customer', 'profit.customer_id', '=', 'customer.id')
            ->select(['profit.*', 'customer.name', 'customer.sn', 'customer.id as customer_id']);

        if ($status) {
            $re->where('profit.status', $status);
        }

        if ($name) {
            $re->where('customer.name', 'like', "%$name%");
        }

        if ($sn) {
            $re->where('customer.sn', 'like', "%$sn%");
        }

        return $re;
    }

    public static function createProfit($customer_id, $bank_id, $bank_account, $bank_account_name, $identity_sn, $img1 = '', $img2 = '', $img3 = '')
    {

        // $img1 = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAMAAABHPGVmAAAAY1BMVEX///8AAAATDQ+cm5v5+fkKAAFfXl5HREVXVVYQCApBQEB2dXVRT0/o6OimpaWwsLDh4eEcFxgjHyDx8fHR0dEYEhSEg4OUk5MxLy88Ozu3trbDw8NoZme9vLxtbGwrKCmLi4uWnTqTAAACwElEQVRoge2Z686bMAyGccCUcCpJCQV6gPu/yrn7gNKJjiQQTZN4f1kV8Ijg2G9czzt06ND/pMvFNSGr4q5LfaecHDhDrkCULhkCo2vfoIDCFaMA+fMKOYraFaTiMCxTjvBwBEF1H6JA8sQRBNAfw5SlbhjBDBIdkANyQA7IAfnnkCBwDIkCXwDAc2dL8QFRTQ2KI1PQu4MIKSH18wQEVA4hcH4FWS1+u4sg2+f7fEJgiDOlQi+v6fPIfG+IkqNVTViXkukDcnxNtitk5lbOZPmw8Vs/BlVvXrMvm5Eg8GPCKsDEC/Kwbp7W1u8rRERDGCq4ATCS9cp9hcBtCEt6KQnxPexQSTvKVwiOFj9DKXlLwaUHFv15/zYInyBMjkY8mcy5/vNfpVAHMn2eC3CjSlAktNW6pPTWIZxbNgM6wilESpiKzfYJX4QgTrs+ZCd9xoPyJczzOwgm35AWpqd9LJcdJBjPh1msZpCZPpbLDkK7YMiXi3QGebJuDEuApTo73yeWkBMLp7jtl+pfAd34sy3EKBNtIVc0mAvYQgpQ+kXIFmJUhLJZahhBLsJgxFHV0/zICOL5y5m7JjOI1wlpMd8yhLSAV+cQLxZg3ktNISWocP2qjRAqYMa91BySoTC8wwLyKi6tc0gghakDNYe8etdSw9oXQjsSzXakDaQE9nQO8e7KbOBsBSne5t0dxKtwcu/uINRYGucQ8qsmjcUS4kkh9NPYFnID7LUvth7xREq7sdD5pLeDGDQWKqm2ox19fwTCeiCWodK7930YsBCtwlnnulo1G4YTTEiNux92Xm1UC6Dhj1IlNk1ZTgJWd+TDOn8HURqvNZaCzrKbGBr+qOTCqF4viRpL95dnlAm9x/YZXg4CmmRZYfcasGpl+YrovMv4shgDuG8fEr6UXaMmXlR6z3f86zlY1n6AQ4cOudUvUaclNsucHaIAAAAASUVORK5CYII=";
        //  self::convertBase64($customer_id, $img);

        if (self::where('customer_id', $customer_id)->get()->first()) {
            return ['success' => '0', 'message' => '重複申請'];
        }

        $customer = Customer::where('id', $customer_id)->get()->first();

        if ($customer->recommend_id) {
            $parent_profit_rate = 20;
            $profit_rate = 80;
        } else {
            $parent_profit_rate = 0;
            $profit_rate = 100;
        }

        $id = self::create([
            'customer_id' => $customer_id,
            'status' => ProfitStatus::Checking()->value,
            'status_title' => ProfitStatus::Checking()->description,
            'bank_id' => $bank_id,
            'bank_account' => $bank_account,
            'bank_account_name' => $bank_account_name,
            'identity_sn' => $identity_sn,
            'img1' => $img1,
            'img2' => $img2,
            'img3' => $img3,
            'parent_profit_rate' => $parent_profit_rate,
            'profit_rate' => $profit_rate,
            'profit_type' => ProfitType::Cash(),

            /*
        'img1' => self::convertBase64($customer_id, $img1),
        'img2' => self::convertBase64($customer_id, $img2),
        'img3' => self::convertBase64($customer_id, $img3),
         */
        ])->id;

        return ['success' => '1', 'id' => $id];
    }

    public static function convertBase64($customer_id, $image)
    {
        if (!$image) {
            return '';
        }

        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);

        $imageName = 'profit_data' . "/" . $customer_id . "/" . uniqid() . '.' . 'png';
        $re = Storage::put($imageName, base64_decode($image));

        return $imageName;

    }

    //回傳分潤資格審核
    public static function getProfitData($customer_id, ProfitStatus $status = null)
    {

        $re = DB::table('usr_customer_profit as cprofit')
            ->leftJoin('usr_customers as customer', 'cprofit.customer_id', '=', 'customer.id')
            ->select(['cprofit.status'
                , 'cprofit.status_title'
                , 'cprofit.parent_profit_rate'
                , 'cprofit.profit_rate'
                , 'cprofit.has_child'
                , 'cprofit.profit_type'
                , 'customer.recommend_id as parent_customer_id'
                , 'customer.sn as mcode'])
            ->where('customer_id', $customer_id);

        if ($status) {
            $re->where('status', $status);
        }

        return $re->get()->first();
    }

    public static function getUser()
    {
        return DB::table('usr_customers as customer')
            ->leftJoin('usr_customer_profit as cp', 'customer.id', '=', 'cp.customer_id')
            ->select(['customer.id', 'customer.name']);
    }

    public static function getFromOldData()
    {
        DB::beginTransaction();
        $_p = DB::table('app_shopping_member')->where('SalesStatus', '3')->get();
        $ucount = 0;
        $parent_relation = [];
        $users = [];
        $step1 = 0;
        $step2 = 0;
        $step3 = 0;
        $step4 = 0;
        $step5 = 0;
        $bankName = [];
        foreach ($_p as $puser) {
            $_u = Customer::where('name', $puser->Name)
                ->where('phone', $puser->Mobile)
                ->get()->first();
            $step1++;
            if ($_u) {
                $users[$puser->MemberID] = $_u->id;
                if (!CustomerProfit::where('customer_id', $_u->id)->get()->first()) {
                    $step2++;
                    $bank = Bank::where('title', "like", "%$puser->BankName%")->get()->first();
                    if ($bank) {
                        $step3++;
                        $re = CustomerProfit::createProfit($_u->id, $bank->id, $puser->BankAcct, $puser->Name, $puser->IDNumber, '', '', '');
                        if ($re['success'] == '1') {
                            $step4++;
                            //  $re['id']
                            CustomerProfit::where('id', $re['id'])->update([
                                'status' => ProfitStatus::Success()->value,
                                'status_title' => ProfitStatus::Success()->description,
                                'has_child' => $puser->RecommandFlag,
                                'profit_rate' => $puser->UPLink ? 80 : 100,
                                'parent_profit_rate' => $puser->UPLink ? 20 : 0,
                            ]);
                            $ucount++;
                            if ($puser->UPLink) {
                                $parent_relation[] = [$re['id'], $puser->UPLink, $_u->id];

                            }

                        }

                    } else {
                        $bankName[] = $puser->BankName;
                    }

                }

            }
        }

        foreach ($parent_relation as $pr) {
            $step5++;
            if (isset($users[$pr[1]])) {
                Customer::where('id', $pr[2])->update([
                    'recommend_id' => $users[$pr[1]],
                ]);

                CustomerProfit::where('id', $pr[0])->update([
                    'parent_customer_id' => $users[$pr[1]],
                ]);

            }
        }

        // 修正帶入

        foreach (CustomerProfit::whereNotNull('parent_customer_id')->get() as $pro) {
            Customer::where('id', $pro->customer_id)->update([
                'recommend_id' => $pro->parent_customer_id,
            ]);
        }

        //  dd($bankName);
        //    dd($step1,$step2,$step3,$step4,$step5);
        DB::commit();
        return $ucount;
    }
}
