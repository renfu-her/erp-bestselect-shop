<?php

namespace App\Imports;

use App\Enums\Discount\DividendFlag;
use App\Models\Customer;
use App\Models\CustomerDividend;
use App\Models\ManualDividend;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class DividendImport implements ToCollection
{

    protected $order_id;
    protected $category;
    protected $file_type;
    protected $sdate;
    protected $edate;

    public function __construct($order_id, $category, $file_type, $sdate, $edate)
    {
        $this->order_id = $order_id;
        $this->category = $category;
        $this->file_type = $file_type;
        $this->sdate = $sdate;
        $this->edate = $edate;
    }
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        //
        if (!in_array($this->file_type, ['sn', 'email'])) {
            dd('error');
            return;
        }

        //  dd($this->file_type);

        $mm = ManualDividend::where('id', $this->order_id)->get()->first();
        $deadline = 0;
        if ($this->edate) {
            $deadline = 1;
        }

        $DividendFlag = DividendFlag::NonActive();

        if (date("Y-m-d") >= $this->sdate) {
            $DividendFlag = DividendFlag::Active();
        }


        foreach ($collection as $key => $value) {
            if ($key != 0) {
                if (isset($value[0]) && isset($value[1])) {
                    $log = [
                        'manual_dividend_id' => $this->order_id,
                        'account' => $value[0],
                        'dividend' => $value[1],
                    ];
                    $customer = Customer::where($this->file_type, $value[0])->get()->first();

                    if ($customer) {
                        if (is_numeric($value[1])) {
                            $note = isset($value[2]) ? $value[2] : '';
                            if ($value[1] > 0) {
                                $id = CustomerDividend::create([
                                    'customer_id' => $customer->id,
                                    'category' => $this->category,
                                    'category_sn' => '',
                                    'dividend' => $value[1],
                                    'deadline' => $deadline,
                                    // 'flag' => DividendFlag::Active(),
                                    // 'flag_title' => DividendFlag::Active()->description,
                                    'flag' => $DividendFlag,
                                    'flag_title' => $DividendFlag->description,
                                    'active_sdate' => $this->sdate,
                                    'active_edate' => $this->edate,
                                    'weight' => 0,
                                    'type' => 'get',
                                    'note' => "手動匯入 " . $mm->note . " " . $note,
                                ])->id;
                                $log['status'] = '1';
                                $log['note'] = $note . '(dividend_id:' . $id . ")";
                            } else if ($value[1] < 0) {
                                $id = CustomerDividend::decrease($customer->id, DividendFlag::Manual(), $value[1], '手動:' . $this->order_id);
                                $log['status'] = '1';
                                $log['note'] = $note . '(dividend_id:' . $id . ")";
                            } else {
                                $log['status'] = '0';
                                $log['note'] = '紅利資料錯誤';
                            }
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
