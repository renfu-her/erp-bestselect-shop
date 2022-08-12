<?php

namespace Database\Seeders;

use App\Enums\Customer\ProfitStatus;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\CustomerProfit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class importErpProfit extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
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
                    $bank = Bank::where('title',"like", "%$puser->BankName%")->get()->first();
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
                                $parent_relation[] = [$re['id'], $puser->UPLink];
                            }

                        }

                    }else{
                        $bankName[]=$puser->BankName;
                    }


                }

            }
        }

        foreach ($parent_relation as $pr) {
            $step5++;
            if (isset($users[$pr[1]])) {
                CustomerProfit::where('id', $pr[0])->update([
                    'parent_cusotmer_id' => $users[$pr[1]],
                ]);
            }
        }
      //  dd($bankName);
     //    dd($step1,$step2,$step3,$step4,$step5);
        DB::commit();
        dd($ucount);
    }
}
