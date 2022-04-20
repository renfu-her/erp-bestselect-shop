<?php

namespace App\Http\Controllers\Cms\AccountManagement;


use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use App\Enums\Received\ReceivedMethod;

use App\Models\Customer;
use App\Models\Order;
use App\Models\GeneralLedger;
use App\Models\OrderItem;
use App\Models\ReceivedOrder;

class AccountReceivedCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * 收款方式
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $id)
    {
        $order_id = request('id');
        $order_data = Order::findOrFail($order_id);

        $order_purchaser = Customer::where([
                'email'=>$order_data->email,
                // 'deleted_at'=>null,
            ])->first();
        $order_list_data = OrderItem::leftJoin('ord_orders', 'ord_orders.id', '=', 'ord_items.order_id')
            ->leftJoin('ord_sub_orders', 'ord_sub_orders.id', '=', 'ord_items.sub_order_id')
            ->where([
                'ord_orders.id'=>$order_id,
            ])
            ->select(
                'ord_orders.id AS order_id',
                'ord_orders.status AS order_status',
                // 'ord_orders.dlv_fee AS order_dlv_fee',

                'ord_sub_orders.sn AS del_sn',
                'ord_sub_orders.ship_category_name AS del_category_name',
                'ord_sub_orders.ship_event AS del_even',
                'ord_sub_orders.ship_temp AS del_temp',

                'ord_items.sku AS product_sku',
                'ord_items.product_title AS product_title',
                'ord_items.price AS product_price',
                'ord_items.qty AS product_qty',
                'ord_items.origin_price AS product_origin_price',
                'ord_items.discount_value AS product_discount',
                'ord_items.discounted_price AS product_after_discounting_price',
            )->get();

        $received_order_collection = ReceivedOrder::where([
            'order_id'=>$order_id,
            'deleted_at'=>null,
        ]);

        if(! $received_order_collection->first()){
            ReceivedOrder::create([
                'order_id'=>$order_id,
                'usr_users_id'=>auth('user')->user() ? auth('user')->user()->id : null,
                'sn'=>'KSG' . date('ymd') . str_pad( count(ReceivedOrder::whereDate('created_at', '=', date('Y-m-d'))->withTrashed()->get()) + 1, 3, '0', STR_PAD_LEFT),
                'price'=>$order_data->total_price,
                // 'tw_dollar'=>0,
                // 'rate'=>1,
                // 'logistics_grade_id'=>ReceivedDefault::where('name', 'logistics')->first()->default_grade_id,
                // 'product_grade_id'=>ReceivedDefault::where('name', 'product')->first()->default_grade_id,
                // 'created_at'=>date("Y-m-d H:i:s"),
            ]);
        }

        $received_order_data = $received_order_collection->get();
        $received_data = DB::table('acc_received')->whereIn('received_order_id', $received_order_data->pluck('id')->toArray())->get([
            'received_type',
            'received_order_id',
            DB::raw('CASE received_method
                WHEN "cash" THEN "現金"
                WHEN "cheque" THEN "支票"
                WHEN "credit_card" THEN "信用卡"
                WHEN "remit" THEN "匯款"
                WHEN "foreign_currency" THEN "外幣"
                WHEN "account_received" THEN "應收帳款"
                WHEN "other" THEN "其它"
                WHEN "refund" THEN "退還" END as received_method'),
            'received_method_id',
            'all_grades_id',
            'tw_price',
            'review_date',
            'accountant_id_fk',
            'note',
        ]);
        $tw_price = $received_order_data->sum('price') - $received_data->sum('tw_price');
        if ($tw_price == 0) {
            // dd('此付款單金額已收齊');
        }

        /**
         * key:收款方式
         * value：true為「收款支付」沒有做預設，會補上全部的會計科目
         */
        $defaultData = [];
        foreach (ReceivedMethod::asArray() as $receivedMethod) {
            $defaultData[$receivedMethod] = DB::table('acc_received_default')
                                                    ->where('name', '=', $receivedMethod)
                                                    ->doesntExistOr(function () use ($receivedMethod) {
                                                        return DB::table('acc_received_default')
                                                            ->where('name', '=', $receivedMethod)
                                                            ->select('default_grade_id')
                                                            ->get();
                                                    });
        }

        $firstGrades = GeneralLedger::getAllFirstGrade();
        $totalGrades = array();
        foreach ($firstGrades as $firstGrade) {
            $totalGrades[] = $firstGrade;
            foreach (GeneralLedger::getSecondGradeById($firstGrade['id']) as $secondGrade) {
                $totalGrades[] = $secondGrade;
                foreach (GeneralLedger::getThirdGradeById($secondGrade['id']) as $thirdGrade) {
                    $totalGrades[] = $thirdGrade;
                    foreach (GeneralLedger::getFourthGradeById($thirdGrade['id']) as $fourthGrade) {
                        $totalGrades[] = $fourthGrade;
                    }
                }
            }
        }

        $allGradeArray = [];
        // $allGrade = AllGrade::all();
        // $gradeModelArray = GradeModelClass::asSelectArray();

        // foreach ($allGrade as $grade) {
        //     $allGradeArray[$grade->id] = [
        //         'grade_id' => $grade->id,
        //         'grade_num' => array_keys($gradeModelArray, $grade->grade_type)[0],
        //         'code' => $grade->eachGrade->code,
        //         'name' => $grade->eachGrade->name,
        //     ];
        // }

        foreach ($totalGrades as $grade) {
            $allGradeArray[$grade['primary_id']] = $grade;
        }
        $defaultArray = [];
        foreach ($defaultData as $recMethod => $ids) {
            // 收款方式若沒有預設、或是方式為「其它」，則自動帶入所有會計科目
            if ($ids !== true &&
                $recMethod !== 'other') {
                foreach ($ids as $id) {
                    $defaultArray[$recMethod][$id->default_grade_id] = [
                        // 'methodName' => $recMethod,
                        'method' => ReceivedMethod::getDescription($recMethod),
                        'grade_id' => $id->default_grade_id,
                        'grade_num' => $allGradeArray[$id->default_grade_id]['grade_num'],
                        'code' => $allGradeArray[$id->default_grade_id]['code'],
                        'name' => $allGradeArray[$id->default_grade_id]['name'],
                    ];
                }
            } else {
                if($recMethod == 'other'){
                    $defaultArray[$recMethod] = $allGradeArray;
                } else {
                    $defaultArray[$recMethod] = [];
                }
            }
        }

        $currencyDefault = DB::table('acc_currency')
            ->leftJoin('acc_received_default', 'acc_currency.received_default_fk', '=', 'acc_received_default.id')
            ->select(
                'acc_currency.name as currency_name',
                'acc_currency.id as currency_id',
                'acc_currency.rate',
                'default_grade_id',
                'acc_received_default.name as method_name'
            )
            ->orderBy('acc_currency.id')
            ->get();
        $currencyDefaultArray = [];
        foreach ($currencyDefault as $default) {
            $currencyDefaultArray[$default->default_grade_id][] = [
                'currency_id'    => $default->currency_id,
                'currency_name'    => $default->currency_name,
                'rate'             => $default->rate,
                'default_grade_id' => $default->default_grade_id,
            ];
        }

        return view('cms.account_management.account_received.edit', [
            'defaultArray' => $defaultArray,
            'currencyDefaultArray' => $currencyDefaultArray,
            'tw_price' => $tw_price,
            'receivedMethods' => ReceivedMethod::asSelectArray(),
            'formAction' => Route('cms.ar.store'),
            'ord_orders_id' => $order_id,


            'breadcrumb_data' => ['id' => $order_data->id, 'sn' => $order_data->sn],
            'order_data' => $order_data,
            'order_purchaser' => $order_purchaser,
            'order_list_data' => $order_list_data,
            'received_order_data' => $received_order_data,
            'received_data' => $received_data,
        ]);
    }

    /**
     * 產生收款單,產生後不能修改收款單，只能刪除再重新產生
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:ord_orders,id',
            'acc_transact_type_fk' => 'required|string|in:cash,cheque,credit_card,remit,foreign_currency,account_received,other,refund',
            'tw_price' => 'required|numeric',
            request('acc_transact_type_fk') => 'required|array',
            request('acc_transact_type_fk') . '.grade' => 'required|exists:acc_all_grades,id',
            'note'=>'nullable|string',
        ]);

        $data = $request->except('_token');
        $received_order_collection = ReceivedOrder::where([
            'order_id'=>$data['id'],
            'deleted_at'=>null,
        ]);
        $received_order_id = $received_order_collection->first()->id;

        DB::beginTransaction();

        try {
            $result_id = ReceivedOrder::store_received($data);

            DB::table('acc_received')->insert([
                'received_type'=>ReceivedOrder::class,
                'received_order_id'=>$received_order_id,
                'received_method'=>$data['acc_transact_type_fk'],
                'received_method_id'=>$result_id,
                'all_grades_id'=>$data[$data['acc_transact_type_fk']]['grade'],
                'tw_price'=>$data['tw_price'],
                'review_date'=>null,
                'accountant_id_fk'=>auth('user')->user()->id,
                'note'=>$data['note'],
                'created_at'=>date("Y-m-d H:i:s"),
            ]);

            DB::commit();
            wToast(__('收款單儲存成功'));

        } catch (\Exception $e) {
            DB::rollback();
            wToast(__('收款單儲存失敗'));
        }

        if ($received_order_collection->sum('price') == DB::table('acc_received')->whereIn('received_order_id', $received_order_collection->pluck('id')->toArray())->sum('tw_price')) {
            return redirect()->route('cms.order.detail', [
                'id' => $data['id'],
            ]);

        } else {
            return redirect()->route('cms.ar.create', [
                'id' => $data['id'],
            ]);
        }
    }

    /**
     * 查看「收款單」
     *
     * @param  \App\Models\AccountReceived  $accountReceived
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AccountReceived  $accountReceived
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountReceived  $accountReceived
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * 收款單「入款審核」
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function review(Request $request)
    {
        return view('cms.account_management.account_received.review', [

        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AccountReceived  $accountReceived
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        //
    }
}