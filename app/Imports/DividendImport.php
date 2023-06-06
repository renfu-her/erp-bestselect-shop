<?php

namespace App\Imports;

use App\Enums\Discount\DividendFlag;
use App\Models\Customer;
use App\Models\CustomerDividend;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class DividendImport implements ToCollection
{

    protected $order_id;
    protected $category;

    public function __construct($order_id, $category)
    {
        $this->order_id = $order_id;
        $this->category = $category;
    }
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        //
        foreach ($collection as $key => $value) {
            if ($key != 0) {
                if (isset($value[0]) && isset($value[1])) {
                    $log = [
                        'manual_dividend_id' => $this->order_id,
                        'account' => $value[0],
                        'dividend' => $value[1],
                    ];
                    $customer = Customer::where('sn', $value[0])->get()->first();

                    if ($customer) {
                        if (is_numeric($value[1]) && $value[1] > 0) {

                            $id = CustomerDividend::create([
                                'customer_id' => $customer->id,
                                'category' => $this->category,
                                'category_sn' => '',
                                'dividend' => $value[1],
                                'deadline' => 0,
                                'flag' => DividendFlag::Active(),
                                'flag_title' => DividendFlag::Active()->description,
                                'weight' => 0,
                                'type' => 'get',
                                'note' => "手動匯入",
                            ])->id;
                            $log['status'] = '1';
                            $log['note'] = 'dividend_id:' . $id;
                        } else {
                            $log['status'] = '0';
                            $log['note'] = '紅利資料錯誤';
                        }
                    } else {
                        $log['status'] = '0';
                        $log['note'] = '帳號錯誤';
                    }

                    DB::table('dis_manual_dividend_log')->insert($log);

                }

            }

        }
       
        // dd($this->param1, $collection);
    }
}
