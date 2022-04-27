<?php

namespace App\Http\Controllers\Cms\AccountManagement;


use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use App\Enums\Received\ReceivedMethod;

use App\Models\AllGrade;
use App\Models\Customer;
use App\Models\Order;
use App\Models\GeneralLedger;
use App\Models\OrderItem;
use App\Models\ReceivedOrder;
use App\Models\ReceivedDefault;
use App\Models\User;

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
        $order_list_data = OrderItem::item_order($order_id)->get();

        $received_order_collection = ReceivedOrder::where([
            'order_id'=>$order_id,
            'deleted_at'=>null,
        ]);

        if(! $received_order_collection->first()){
            ReceivedOrder::create([
                'order_id'=>$order_id,
                'usr_users_id'=>auth('user')->user() ? auth('user')->user()->id : null,
                'sn'=>'MSG' . date('ymd') . str_pad( count(ReceivedOrder::whereDate('created_at', '=', date('Y-m-d'))->withTrashed()->get()) + 1, 3, '0', STR_PAD_LEFT),
                'price'=>$order_data->total_price,
                // 'tw_dollar'=>0,
                // 'rate'=>1,
                'logistics_grade_id'=>ReceivedDefault::where('name', 'logistics')->first()->default_grade_id,
                'product_grade_id'=>ReceivedDefault::where('name', 'product')->first()->default_grade_id,
                // 'created_at'=>date("Y-m-d H:i:s"),
            ]);
        }

        $received_order_data = $received_order_collection->get();
        $received_data = DB::table('acc_received')->whereIn('received_order_id', $received_order_data->pluck('id')->toArray())->get();
        foreach($received_data as $value){
            $value->received_method_name = ReceivedMethod::getDescription($value->received_method);
            $value->account = AllGrade::find($value->all_grades_id)->eachGrade;

            if($value->received_method == 'foreign_currency'){
                $arr = explode('-', AllGrade::find($value->all_grades_id)->eachGrade->name);
                $value->currency_name = $arr[0] == '外幣' ? $arr[1] . ' - ' . $arr[2] : 'NTD';
                $value->currency_rate = DB::table('acc_received_currency')->find($value->received_method_id)->currency;
            } else {
                $value->currency_name = 'NTD';
                $value->currency_rate = 1;
            }
        }
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


    public function receipt(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);
        $request->validate([
            'id' => 'required|exists:ord_orders,id',
        ]);

        $order = Order::findOrFail(request('id'));
        $received_order_collection = ReceivedOrder::where([
            'order_id'=>request('id'),
            'deleted_at'=>null,
        ]);

        $received_order_data = $received_order_collection->get();
        if (count($received_order_data) == 0 || $received_order_collection->sum('price') != DB::table('acc_received')->whereIn('received_order_id', $received_order_collection->pluck('id')->toArray())->sum('tw_price')) {
            return abort(404);
        }

        $order_list_data = OrderItem::item_order(request('id'))->get();
        $product_qc = $order_list_data->pluck('product_user_name')->toArray();
        $product_qc = array_unique($product_qc);
        asort($product_qc);

        $received_data = DB::table('acc_received')->whereIn('received_order_id', $received_order_data->pluck('id')->toArray())->get();
        $order_purchaser = Customer::where([
            'email'=>$order->email,
            // 'deleted_at'=>null,
        ])->first();
        $undertaker = User::find($received_order_collection->first()->usr_users_id);

        $accountant = User::whereIn('id', $received_data->pluck('accountant_id_fk')->toArray())->get();
        $accountant = array_unique($accountant->pluck('name')->toArray());
        asort($accountant);

        $product_grade_name = AllGrade::find($received_order_collection->first()->product_grade_id)->eachGrade->code . ' - ' . AllGrade::find($received_order_collection->first()->product_grade_id)->eachGrade->name;
        $logistics_grade_name = AllGrade::find($received_order_collection->first()->logistics_grade_id)->eachGrade->code . ' - ' . AllGrade::find($received_order_collection->first()->logistics_grade_id)->eachGrade->name;

        return view('cms.account_management.account_received.receipt', [
            'received_order'=>$received_order_collection->first(),
            'order'=>$order,
            'order_list_data' => $order_list_data,
            'received_data' => $received_data,
            'order_purchaser' => $order_purchaser,
            'undertaker'=>$undertaker,
            'product_qc'=>implode(',', $product_qc),
            'accountant'=>implode(',', $accountant),
            'product_grade_name'=>$product_grade_name,
            'logistics_grade_name'=>$logistics_grade_name,

            'breadcrumb_data' => ['id'=>$order->id, 'sn'=>$order->sn],
        ]);
    }


    public function review(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);
        $request->validate([
            'id' => 'required|exists:ord_orders,id',
        ]);

        $received_order_collection = ReceivedOrder::where([
            'order_id'=>request('id'),
            'deleted_at'=>null,
        ]);

        $received_order_data = $received_order_collection->get();
        if (count($received_order_data) == 0 || $received_order_collection->sum('price') != DB::table('acc_received')->whereIn('received_order_id', $received_order_collection->pluck('id')->toArray())->sum('tw_price')) {
            return abort(404);
        }

        $received_order = $received_order_collection->first();

        if($request->isMethod('post')){
            $request->validate([
                'receipt_date' => 'required|date_format:"Y-m-d"',
                'invoice_number' => 'required|string',
            ]);

            $received_order->update([
                'receipt_date'=>request('receipt_date'),
                'invoice_number'=>request('invoice_number'),
            ]);

            wToast(__('入帳日期更新成功'));
            return redirect()->route('cms.ar.receipt', ['id'=>request('id')]);

        } else if($request->isMethod('get')){
            if($received_order->receipt_date){
                $received_order->update([
                    'receipt_date'=>null,
                ]);

                wToast(__('入帳日期已取消'));
                return redirect()->route('cms.ar.receipt', ['id'=>request('id')]);

            } else {
                $undertaker = User::find($received_order->usr_users_id);
                $order = Order::findOrFail(request('id'));

                $order_list_data = OrderItem::item_order(request('id'))->get();

                $received_data = DB::table('acc_received')->whereIn('received_order_id', $received_order_data->pluck('id')->toArray())->get();
                foreach($received_data as $value){
                    $value->received_method_name = ReceivedMethod::getDescription($value->received_method);
                    $value->account = AllGrade::find($value->all_grades_id)->eachGrade;

                    if($value->received_method == 'foreign_currency'){
                        $arr = explode('-', AllGrade::find($value->all_grades_id)->eachGrade->name);
                        $value->currency_name = $arr[0] == '外幣' ? $arr[1] . ' - ' . $arr[2] : 'NTD';
                        $value->currency_rate = DB::table('acc_received_currency')->find($value->received_method_id)->currency;
                    } else {
                        $value->currency_name = 'NTD';
                        $value->currency_rate = 1;
                    }
                }

                $product_grade_name = AllGrade::find($received_order->product_grade_id)->eachGrade->code . ' - ' . AllGrade::find($received_order->product_grade_id)->eachGrade->name;
                $logistics_grade_name = AllGrade::find($received_order->logistics_grade_id)->eachGrade->code . ' - ' . AllGrade::find($received_order->logistics_grade_id)->eachGrade->name;

                return view('cms.account_management.account_received.review', [
                    'form_action'=>route('cms.ar.review' , ['id'=>request('id')]),
                    'received_order'=>$received_order,
                    'order'=>$order,
                    'order_list_data' => $order_list_data,
                    'received_data' => $received_data,
                    'undertaker'=>$undertaker,
                    'product_grade_name'=>$product_grade_name,
                    'logistics_grade_name'=>$logistics_grade_name,

                    'breadcrumb_data' => ['id'=>$order->id, 'sn'=>$order->sn],
                ]);
            }
        }
    }
}