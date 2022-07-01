<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use App\Enums\Discount\DisCategory;
use App\Enums\Order\PaymentStatus;
use App\Enums\Order\OrderStatus;
use App\Enums\Received\ReceivedMethod;

use App\Models\AllGrade;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\GeneralLedger;
use App\Models\Order;
use App\Models\OrderFlow;
use App\Models\OrderItem;
use App\Models\OrderPayCreditCard;
use App\Models\Product;
use App\Models\ReceivedDefault;
use App\Models\ReceivedOrder;
use App\Models\User;

class AccountReceivedCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();
        $page = getPageCount(Arr::get($query, 'data_per_page', 10)) > 0 ? getPageCount(Arr::get($query, 'data_per_page', 10)) : 10;

        $check_review_status = [
            'all'=>'不限',
            '0'=>'入款未審核',
            '1'=>'入款已審核',
        ];

        $cond = [];

        $cond['customer_id'] = Arr::get($query, 'customer_id', []);
        if (gettype($cond['customer_id']) == 'string') {
            $cond['customer_id'] = explode(',', $cond['customer_id']);
        } else {
            $cond['customer_id'] = [];
        }

        $cond['r_order_sn'] = Arr::get($query, 'r_order_sn', null);
        $cond['order_sn'] = Arr::get($query, 'order_sn', null);

        $cond['r_order_min_price'] = Arr::get($query, 'r_order_min_price', null);
        $cond['r_order_max_price'] = Arr::get($query, 'r_order_max_price', null);
        $r_order_price = [
            $cond['r_order_min_price'],
            $cond['r_order_max_price']
        ];

        $cond['r_order_sdate'] = Arr::get($query, 'r_order_sdate', null);
        $cond['r_order_edate'] = Arr::get($query, 'r_order_edate', null);
        $r_order_receipt_date = [
            $cond['r_order_sdate'],
            $cond['r_order_edate']
        ];

        $cond['order_sdate'] = Arr::get($query, 'order_sdate', null);
        $cond['order_edate'] = Arr::get($query, 'order_edate', null);
        $received_date = [
            $cond['order_sdate'],
            $cond['order_edate']
        ];

        $cond['check_review'] = Arr::get($query, 'check_review', 'all');

        $dataList = ReceivedOrder::received_order_list(
            $cond['customer_id'],
            $cond['r_order_sn'],
            $cond['order_sn'],
            $r_order_price,
            $r_order_receipt_date,
            $received_date,
            $cond['check_review'],
        )->paginate($page)->appends($query);

        // accounting classification start
        foreach($dataList as $value){
            $debit = [];
            $credit = [];

            // 收款項目
            foreach(json_decode($value->received_list) as $r_value){
                $received_method_name = ReceivedMethod::getDescription($r_value->received_method);
                $received_account = AllGrade::find($r_value->all_grades_id)->eachGrade;

                if($r_value->received_method == 'foreign_currency'){
                    $arr = explode('-', AllGrade::find($r_value->all_grades_id)->eachGrade->name);
                    $r_value->currency_name = $arr[0] == '外幣' ? $arr[1] . ' - ' . $arr[2] : 'NTD';
                    $r_value->currency_rate = DB::table('acc_received_currency')->find($r_value->received_method_id)->currency;
                } else {
                    $r_value->currency_name = 'NTD';
                    $r_value->currency_rate = 1;
                }

                $name = $received_method_name . ' ' . $r_value->summary . '（' . $received_account->code . ' - ' . $received_account->name . '）';

                $tmp = [
                    'account_code'=>$received_account->code,
                    'name'=>$name,
                    'price'=>$r_value->tw_price,
                    'type'=>'r',
                    'd_type'=>'received',

                    'account_name'=>$received_account->name,
                    'method_name'=>$received_method_name,
                    'summary'=>$r_value->summary,
                    'note'=>$r_value->note,
                    'product_title'=>null,
                    'del_even'=>null,
                    'del_category_name'=>null,
                    'product_price'=>null,
                    'product_qty'=>null,
                    'product_owner'=>null,
                    'discount_title'=>null,
                    'payable_type'=>null,
                    'received_info'=>$r_value,
                ];
                GeneralLedger::classification_processing($debit, $credit, $tmp);
            }

            // 商品
            $product_account = AllGrade::find($value->ro_product_grade_id) ? AllGrade::find($value->ro_product_grade_id)->eachGrade : null;
            $account_code = $product_account ? $product_account->code : '4000';
            $account_name = $product_account ? $product_account->name : '無設定會計科目';
            $product_name = $account_code . ' - ' . $account_name;
            foreach(json_decode($value->order_item) as $o_value){
                $name = $product_name . ' --- ' . $o_value->product_title . '（' . $o_value->price . ' * ' . $o_value->qty . '）';

                $tmp = [
                    'account_code'=>$account_code,
                    'name'=>$name,
                    'price'=>$o_value->origin_price,
                    'type'=>'r',
                    'd_type'=>'product',

                    'account_name'=>$account_name,
                    'method_name'=>null,
                    'summary'=>null,
                    'note'=>null,
                    'product_title'=>$o_value->product_title,
                    'del_even'=>null,
                    'del_category_name'=>null,
                    'product_price'=>$o_value->price,
                    'product_qty'=>$o_value->qty,
                    'product_owner'=>null,
                    'discount_title'=>null,
                    'payable_type'=>null,
                    'received_info'=>null,
                ];
                GeneralLedger::classification_processing($debit, $credit, $tmp);
            }

            // 物流
            if($value->order_dlv_fee <> 0){
                $log_account = AllGrade::find($value->ro_logistics_grade_id) ? AllGrade::find($value->ro_logistics_grade_id)->eachGrade : null;
                $account_code = $log_account ? $log_account->code : '4000';
                $account_name = $log_account ? $log_account->name : '無設定會計科目';
                $name = $account_code . ' - ' . $account_name;

                $tmp = [
                    'account_code'=>$account_code,
                    'name'=>$name,
                    'price'=>$value->order_dlv_fee,
                    'type'=>'r',
                    'd_type'=>'logistics',

                    'account_name'=>$account_name,
                    'method_name'=>null,
                    'summary'=>null,
                    'note'=>null,
                    'product_title'=>null,
                    'del_even'=>null,
                    'del_category_name'=>null,
                    'product_price'=>null,
                    'product_qty'=>null,
                    'product_owner'=>null,
                    'discount_title'=>null,
                    'payable_type'=>null,
                    'received_info'=>null,
                ];
                GeneralLedger::classification_processing($debit, $credit, $tmp);
            }

            // 折扣
            if($value->order_discount_value > 0){
                foreach(json_decode($value->order_discount) ?? [] as $d_value){
                    $dis_account = AllGrade::find($d_value->discount_grade_id) ? AllGrade::find($d_value->discount_grade_id)->eachGrade : null;
                    $account_code = $dis_account ? $dis_account->code : '4000';
                    $account_name = $dis_account ? $dis_account->name : '無設定會計科目';
                    $name = $account_code . ' - ' . $account_name;

                    $tmp = [
                        'account_code'=>$account_code,
                        'name'=>$name,
                        'price'=>$d_value->discount_value,
                        'type'=>'r',
                        'd_type'=>'discount',

                        'account_name'=>$account_name,
                        'method_name'=>null,
                        'summary'=>null,
                        'note'=>null,
                        'product_title'=>null,
                        'del_even'=>null,
                        'del_category_name'=>null,
                        'product_price'=>null,
                        'product_qty'=>null,
                        'product_owner'=>null,
                        'discount_title'=>$d_value->title,
                        'payable_type'=>null,
                        'received_info'=>null,
                    ];
                    GeneralLedger::classification_processing($debit, $credit, $tmp);
                }
            }

            $value->debit = $debit;
            $value->credit = $credit;
        }
        // accounting classification end

        return view('cms.account_management.account_received.list', [
            'data_per_page' => $page,
            'dataList' => $dataList,
            'cond' => $cond,
            'customer' => Customer::whereNull('deleted_at')->toBase()->get(),
            'check_review_status' => $check_review_status,
        ]);
    }


    // only for order used
    public function create(Request $request, $id)
    {
        $order_id = request('id');
        $order_data = Order::findOrFail($order_id);

        $order_purchaser = Customer::where([
                'email'=>$order_data->email,
                // 'deleted_at'=>null,
            ])->first();
        $order_list_data = OrderItem::item_order($order_id)->get();

        $source_type = app(Order::class)->getTable();
        $received_order_collection = ReceivedOrder::where([
            'source_type'=>$source_type,
            'source_id'=>$order_id,
        ]);

        if(! $received_order_collection->first()){
            ReceivedOrder::create_received_order($source_type, $order_id);
        }

        $received_order_data = $received_order_collection->get();
        $received_data = ReceivedOrder::get_received_detail($received_order_data->pluck('id')->toArray());

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

        $total_grades = GeneralLedger::total_grade_list();

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

        foreach ($total_grades as $grade) {
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

        $order_discount = DB::table('ord_discounts')->where([
            'order_type'=>'main',
            'order_id'=>$order_id,
        ])->where('discount_value', '>', 0)->get()->toArray();

        foreach($order_discount as $value){
            $value->account_code = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->code : '4000';
            $value->account_name = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->name : '無設定會計科目';
        }

        $card_type = [
            'visa'=>'VISA',
            'jcb'=>'JCB',
            'master'=>'MASTER',
            'american_express'=>'美國運通卡',
            'union_pay'=>'銀聯卡',
        ];
        $checkout_area = [
            'taipei'=>'台北',
        ];

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
            'order_discount'=>$order_discount,
            'received_order_data' => $received_order_data,
            'received_data' => $received_data,
            'card_type'=>$card_type,
            'checkout_area'=>$checkout_area,
        ]);
    }


    // only for order used
    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:ord_orders,id',
            'acc_transact_type_fk' => 'required|string|in:' . implode(',', ReceivedMethod::asArray()),
            'tw_price' => 'required|numeric',
            request('acc_transact_type_fk') => 'required|array',
            request('acc_transact_type_fk') . '.grade' => 'required|exists:acc_all_grades,id',
            'summary'=>'nullable|string',
            'note'=>'nullable|string',
        ]);

        $data = $request->except('_token');
        $source_type = app(Order::class)->getTable();
        $received_order_collection = ReceivedOrder::where([
            'source_type'=>$source_type,
            'source_id'=>$data['id'],
        ]);
        $received_order_id = $received_order_collection->first()->id;

        DB::beginTransaction();

        try {
            // 'credit_card'
            if($data['acc_transact_type_fk'] == ReceivedMethod::CreditCard){
                $card_type = [
                    'visa'=>'VISA',
                    'jcb'=>'JCB',
                    'master'=>'MASTER',
                    'american_express'=>'美國運通卡',
                    'union_pay'=>'銀聯卡',
                ];
                $checkout_area = [
                    'taipei'=>'台北',
                ];

                $data[$data['acc_transact_type_fk']] = [
                    'cardnumber'=>$data[$data['acc_transact_type_fk']]['cardnumber'],
                    'authamt'=>$data['tw_price'] ?? 0,
                    'ckeckout_date'=>$data[$data['acc_transact_type_fk']]['ckeckout_date'] ?? null,// date("Y-m-d H:i:s")
                    'card_type_code'=>$data[$data['acc_transact_type_fk']]['card_type_code'] ?? null,
                    'card_type'=>$card_type[$data[$data['acc_transact_type_fk']]['card_type_code']] ?? null,
                    'card_owner_name'=>$data[$data['acc_transact_type_fk']]['card_owner_name'] ?? null,
                    'authcode'=>$data[$data['acc_transact_type_fk']]['authcode'] ?? null,
                    'all_grades_id'=>$data[$data['acc_transact_type_fk']]['grade'],
                    'checkout_area_code'=>'taipei',// $data[$data['acc_transact_type_fk']]['credit_card_area_code']
                    'checkout_area'=>'台北',// $checkout_area[$data[$data['acc_transact_type_fk']]['credit_card_area_code']]
                    'installment'=>$data[$data['acc_transact_type_fk']]['installment'] ?? 'none',
                    'requested'=>'n',
                    'card_nat'=>'local',
                    'checkout_mode'=>'offline',
                ];

                $data[$data['acc_transact_type_fk']]['grade'] = $data[$data['acc_transact_type_fk']]['all_grades_id'];

                $EncArray['more_info'] = $data[$data['acc_transact_type_fk']];
            }

            $result_id = ReceivedOrder::store_received_method($data);

            $parm = [];
            $parm['received_order_id'] = $received_order_id;
            $parm['received_method'] = $data['acc_transact_type_fk'];
            $parm['received_method_id'] = $result_id;
            $parm['grade_id'] = $data[$data['acc_transact_type_fk']]['grade'];
            $parm['price'] = $data['tw_price'];
            $parm['accountant_id_fk'] = auth('user')->user()->id;
            $parm['summary'] = $data['summary'];
            $parm['note'] = $data['note'];
            ReceivedOrder::store_received($parm);

            if($data['acc_transact_type_fk'] == ReceivedMethod::CreditCard){
                OrderPayCreditCard::create_log($source_type, $data['id'], (object) $EncArray);
            }

            DB::commit();
            wToast(__('收款單儲存成功'));

        } catch (\Exception $e) {
            DB::rollback();
            wToast(__('收款單儲存失敗'));
        }

        if (ReceivedOrder::find($received_order_id) && ReceivedOrder::find($received_order_id)->balance_date) {
            return redirect()->route('cms.order.detail', [
                'id' => $data['id'],
            ]);

        } else {
            return redirect()->route('cms.ar.create', [
                'id' => $data['id'],
            ]);
        }
    }


    // only for order used
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
            'source_type'=>app(Order::class)->getTable(),
            'source_id'=>$id,
        ]);

        $received_order_data = $received_order_collection->get();
        if (count($received_order_data) == 0 || !$received_order_collection->first()->balance_date) {
            return abort(404);
        }

        $order_list_data = OrderItem::item_order(request('id'))->get();
        $product_qc = $order_list_data->pluck('product_user_name')->toArray();
        $product_qc = array_unique($product_qc);
        asort($product_qc);

        $received_data = ReceivedOrder::get_received_detail($received_order_data->pluck('id')->toArray());

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

        $order_discount = DB::table('ord_discounts')->where([
                'order_type'=>'main',
                'order_id'=>request('id'),
            ])->where('discount_value', '>', 0)->get()->toArray();

        foreach($order_discount as $value){
            $value->account_code = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->code : '4000';
            $value->account_name = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->name : '無設定會計科目';
        }

        return view('cms.account_management.account_received.receipt', [
            'received_order'=>$received_order_collection->first(),
            'order'=>$order,
            'order_discount'=>$order_discount,
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


    // only for order used
    public function review(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);
        $request->validate([
            'id' => 'required|exists:ord_orders,id',
        ]);

        $received_order_collection = ReceivedOrder::where([
            'source_type'=>app(Order::class)->getTable(),
            'source_id'=>$id,
        ]);

        $received_order_data = $received_order_collection->get();
        if (count($received_order_data) == 0 || !$received_order_collection->first()->balance_date) {
            return abort(404);
        }

        $received_order = $received_order_collection->first();

        if($request->isMethod('post')){
            $request->validate([
                'receipt_date' => 'required|date_format:"Y-m-d"',
                'invoice_number' => 'nullable|string',
            ]);

            $received_order->update([
                'receipt_date'=>request('receipt_date'),
                'invoice_number'=>request('invoice_number'),
            ]);

            if( in_array(request('received_method'), ReceivedMethod::asArray()) && is_array(request(request('received_method')))){
                $req = request(request('received_method'));
                foreach($req as $r){
                    $r['received_method'] = request('received_method');
                    ReceivedOrder::update_received_method($r);
                }
            }

            OrderFlow::changeOrderStatus($id, OrderStatus::Received());
            // 配發啟用日期
            Order::assign_dividend_active_date($id);

            wToast(__('入帳日期更新成功'));
            return redirect()->route('cms.ar.receipt', ['id'=>request('id')]);

        } else if($request->isMethod('get')){
            if($received_order->receipt_date){
                $received_order->update([
                    'receipt_date'=>null,
                ]);

                OrderFlow::changeOrderStatus($id, OrderStatus::Paided());
                wToast(__('入帳日期已取消'));
                return redirect()->route('cms.ar.receipt', ['id'=>request('id')]);

            } else {
                $undertaker = User::find($received_order->usr_users_id);
                $order = Order::findOrFail(request('id'));

                $order_list_data = OrderItem::item_order(request('id'))->get();

                $received_data = ReceivedOrder::get_received_detail($received_order_data->pluck('id')->toArray());

                $debit = [];
                $credit = [];

                // 收款項目
                foreach($received_data as $value){
                    $name = $value->received_method_name . ' ' . $value->summary . '（' . $value->account->code . ' - ' . $value->account->name . '）';
                    // GeneralLedger::classification_processing($debit, $credit, $value->master_account->code, $name, $value->tw_price, 'r', 'received');

                    $tmp = [
                        'account_code'=>$value->account->code,
                        'name'=>$name,
                        'price'=>$value->tw_price,
                        'type'=>'r',
                        'd_type'=>'received',

                        'account_name'=>$value->account->name,
                        'method_name'=>$value->received_method_name,
                        'summary'=>$value->summary,
                        'note'=>$value->note,
                        'product_title'=>null,
                        'del_even'=>null,
                        'del_category_name'=>null,
                        'product_price'=>null,
                        'product_qty'=>null,
                        'product_owner'=>null,
                        'discount_title'=>null,
                        'payable_type'=>null,

                        'received_info'=>$value,
                    ];
                    GeneralLedger::classification_processing($debit, $credit, $tmp);
                }

                // 商品
                $product_account = AllGrade::find($received_order->product_grade_id) ? AllGrade::find($received_order->product_grade_id)->eachGrade : null;
                $account_code = $product_account ? $product_account->code : '4000';
                $account_name = $product_account ? $product_account->name : '無設定會計科目';
                $product_grade_name = $account_code . ' - ' . $account_name;
                foreach($order_list_data as $value){
                    $name = $product_grade_name . ' --- ' . $value->product_title . '（' . $value->del_even . ' - ' . $value->del_category_name . '）（' . $value->product_price . ' * ' . $value->product_qty . '）';
                    // GeneralLedger::classification_processing($debit, $credit, $product_master_account->code, $name, $value->product_origin_price, 'r', 'product');

                    $tmp = [
                        'account_code'=>$account_code,
                        'name'=>$name,
                        'price'=>$value->product_origin_price,
                        'type'=>'r',
                        'd_type'=>'product',

                        'account_name'=>$account_name,
                        'method_name'=>null,
                        'summary'=>$value->summary,
                        'note'=>$value->note,
                        'product_title'=>$value->product_title,
                        'del_even'=>$value->del_even,
                        'del_category_name'=>$value->del_category_name,
                        'product_price'=>$value->product_price,
                        'product_qty'=>$value->product_qty,
                        'product_owner'=>null,
                        'discount_title'=>null,
                        'payable_type'=>null,
                        'received_info'=>null,
                    ];
                    GeneralLedger::classification_processing($debit, $credit, $tmp);
                }

                // 物流
                if($order->dlv_fee <> 0){
                    $log_account = AllGrade::find($received_order->logistics_grade_id) ? AllGrade::find($received_order->logistics_grade_id)->eachGrade : null;
                    $account_code = $log_account ? $log_account->code : '4000';
                    $account_name = $log_account ? $log_account->name : '無設定會計科目';
                    // $name = $logistics_grade_name = $account_code . ' - ' . $account_name;
                    $name = $account_code . ' - ' . $account_name;
                    // GeneralLedger::classification_processing($debit, $credit, $logistics_master_account->code, $name, $order->dlv_fee, 'r', 'logistics');

                    $tmp = [
                        'account_code'=>$account_code,
                        'name'=>$name,
                        'price'=>$order->dlv_fee,
                        'type'=>'r',
                        'd_type'=>'logistics',

                        'account_name'=>$account_name,
                        'method_name'=>null,
                        'summary'=>null,
                        'note'=>null,
                        'product_title'=>null,
                        'del_even'=>null,
                        'del_category_name'=>null,
                        'product_price'=>null,
                        'product_qty'=>null,
                        'product_owner'=>null,
                        'discount_title'=>null,
                        'payable_type'=>null,
                        'received_info'=>null,
                    ];
                    GeneralLedger::classification_processing($debit, $credit, $tmp);
                }

                // 折扣
                if($order->discount_value > 0){
                    $order_discount = DB::table('ord_discounts')->where([
                        'order_type'=>'main',
                        'order_id'=>request('id'),
                    ])->where('discount_value', '>', 0)->get()->toArray();

                    foreach($order_discount as $value){
                        $dis_account = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade : null;
                        $account_code = $dis_account ? $dis_account->code : '4000';
                        $account_name = $dis_account ? $dis_account->name : '無設定會計科目';
                        $name = $account_code . ' - ' . $account_name . ' - ' . $value->title;
                        // GeneralLedger::classification_processing($debit, $credit, 4, $name, $order->discount_value, 'r', 'discount');

                        $tmp = [
                            'account_code'=>$account_code,
                            'name'=>$name,
                            'price'=>$value->discount_value,
                            'type'=>'r',
                            'd_type'=>'discount',

                            'account_name'=>$account_name,
                            'method_name'=>null,
                            'summary'=>null,
                            'note'=>null,
                            'product_title'=>null,
                            'del_even'=>null,
                            'del_category_name'=>null,
                            'product_price'=>null,
                            'product_qty'=>null,
                            'product_owner'=>null,
                            'discount_title'=>$value->title,
                            'payable_type'=>null,
                            'received_info'=>null,
                    ];
                        GeneralLedger::classification_processing($debit, $credit, $tmp);
                    }
                }

                $card_type = [
                    'visa'=>'VISA',
                    'jcb'=>'JCB',
                    'master'=>'MASTER',
                    'american_express'=>'美國運通卡',
                    'union_pay'=>'銀聯卡',
                ];
                $checkout_area = [
                    'taipei'=>'台北',
                ];

                // grade process start
                $defaultData = [];
                foreach (ReceivedMethod::asArray() as $receivedMethod) {
                    $defaultData[$receivedMethod] = DB::table('acc_received_default')->where('name', '=', $receivedMethod)
                        ->doesntExistOr(function () use ($receivedMethod) {
                            return DB::table('acc_received_default')->where('name', '=', $receivedMethod)
                                ->select('default_grade_id')
                                ->get();
                        });
                }

                $total_grades = GeneralLedger::total_grade_list();
                $allGradeArray = [];

                foreach ($total_grades as $grade) {
                    $allGradeArray[$grade['primary_id']] = $grade;
                }
                $default_grade = [];
                foreach ($defaultData as $recMethod => $ids) {
                    if ($ids !== true &&
                        $recMethod !== 'other') {
                        foreach ($ids as $id) {
                            $default_grade[$recMethod][$id->default_grade_id] = [
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
                            $default_grade[$recMethod] = $allGradeArray;
                        } else {
                            $default_grade[$recMethod] = [];
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
                $currency_default_grade = [];
                foreach ($currencyDefault as $default) {
                    $currency_default_grade[$default->default_grade_id][] = [
                        'currency_id'    => $default->currency_id,
                        'currency_name'    => $default->currency_name,
                        'rate'             => $default->rate,
                        'default_grade_id' => $default->default_grade_id,
                    ];
                }
                // grade process end

                return view('cms.account_management.account_received.review', [
                    'form_action'=>route('cms.ar.review' , ['id'=>request('id')]),
                    'received_order'=>$received_order,
                    'order'=>$order,
                    'order_list_data'=>$order_list_data,
                    'received_data'=>$received_data,
                    'undertaker'=>$undertaker,
                    'product_grade_name'=>$product_grade_name,
                    // 'logistics_grade_name'=>$logistics_grade_name,
                    'debit'=>$debit,
                    'credit'=>$credit,
                    'card_type'=>$card_type,
                    'checkout_area'=>$checkout_area,
                    'credit_card_grade'=>$default_grade[ReceivedMethod::CreditCard],
                    // 'default_grade'=>$default_grade,
                    // 'currency_default_grade'=>$currency_default_grade,

                    'breadcrumb_data' => ['id'=>$order->id, 'sn'=>$order->sn],
                ]);
            }
        }
    }


    // only for order used
    public function taxation(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);
        $request->validate([
            'id' => 'required|exists:ord_orders,id',
        ]);

        $received_order_collection = ReceivedOrder::where([
            'source_type'=>app(Order::class)->getTable(),
            'source_id'=>$id,
        ]);

        $received_order_data = $received_order_collection->get();
        if (count($received_order_data) == 0 || !$received_order_collection->first()->balance_date) {
            return abort(404);
        }

        $received_order = $received_order_collection->first();

        if($request->isMethod('post')){
            $request->validate([
                'received' => 'required|array',
                'product_grade_id' => 'required|exists:acc_all_grades,id',
                'product' => 'required|array',
                'logistics_grade_id' => 'nullable|exists:acc_all_grades,id',
                'order_dlv' => 'nullable|array',
                'discount' => 'nullable|array',
            ]);

            DB::beginTransaction();

            try {
                $received_order->update([
                    'logistics_grade_id'=>request('logistics_grade_id'),
                    'product_grade_id'=>request('product_grade_id'),
                ]);

                if(request('received') && is_array(request('received'))){
                    $received = request('received');
                    foreach($received as $key => $value){
                        $value['received_id'] = $key;
                        ReceivedOrder::update_received($value);
                    }
                }

                if(request('product') && is_array(request('product'))){
                    $product = request('product');
                    foreach($product as $key => $value){
                        $value['product_id'] = $key;
                        Product::update_product_taxation($value);
                    }
                }

                if(request('order_dlv') && is_array(request('order_dlv'))){
                    $order = request('order_dlv');
                    foreach($order as $key => $value){
                        $value['order_id'] = $key;
                        Order::update_dlv_taxation($value);
                    }
                }

                if(request('discount') && is_array(request('discount'))){
                    $discount = request('discount');
                    foreach($discount as $key => $value){
                        $value['discount_id'] = $key;
                        Discount::update_order_discount_taxation($value);
                    }
                }

                DB::commit();
                wToast(__('摘要/稅別更新成功'));

            } catch (\Exception $e) {
                DB::rollback();
                wToast(__('摘要/稅別更新失敗'));
            }

            return redirect()->route('cms.ar.receipt', ['id'=>request('id')]);

        } else if($request->isMethod('get')){

            $order = Order::findOrFail(request('id'));
            $order_list_data = OrderItem::item_order(request('id'))->get();
            $order_discount = DB::table('ord_discounts')->where([
                'order_type'=>'main',
                'order_id'=>request('id'),
            ])->where('discount_value', '>', 0)->get()->toArray();

            $received_data = ReceivedOrder::get_received_detail($received_order_data->pluck('id')->toArray());

            // grade process start
            $defaultData = [];
            foreach (ReceivedMethod::asArray() as $receivedMethod) {
                $defaultData[$receivedMethod] = DB::table('acc_received_default')->where('name', '=', $receivedMethod)
                    ->doesntExistOr(function () use ($receivedMethod) {
                        return DB::table('acc_received_default')->where('name', '=', $receivedMethod)
                            ->select('default_grade_id')
                            ->get();
                    });
            }

            $total_grades = GeneralLedger::total_grade_list();
            $allGradeArray = [];

            foreach ($total_grades as $grade) {
                $allGradeArray[$grade['primary_id']] = $grade;
            }
            $default_grade = [];
            foreach ($defaultData as $recMethod => $ids) {
                if ($ids !== true &&
                    $recMethod !== 'other') {
                    foreach ($ids as $id) {
                        $default_grade[$recMethod][$id->default_grade_id] = [
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
                        $default_grade[$recMethod] = $allGradeArray;
                    } else {
                        $default_grade[$recMethod] = [];
                    }
                }
            }

            $default_product_grade = ReceivedDefault::where('name', 'product')->first() ? ReceivedDefault::where('name', 'product')->first()->default_grade_id : null;
            $default_logistics_grade = ReceivedDefault::where('name', 'logistics')->first() ? ReceivedDefault::where('name', 'logistics')->first()->default_grade_id : null;

            $discount_category = DisCategory::asArray();
            $discount_type = [];
            foreach($discount_category as $dis_value){
                $discount_type[$dis_value] = DisCategory::getDescription($dis_value);
            }
            ksort($discount_type);

            $default_discount_grade = [];
            foreach($discount_type as $key => $value){
                $default_discount_grade[$key] =  ReceivedDefault::where('name', $key)->first() ? ReceivedDefault::where('name', $key)->first()->default_grade_id : null;
            }
            // grade process end

            return view('cms.account_management.account_received.taxation', [
                'form_action'=>route('cms.ar.taxation' , ['id'=>request('id')]),
                'received_order'=>$received_order,
                'order'=>$order,
                'order_discount'=>$order_discount,
                'order_list_data'=>$order_list_data,
                'received_data' => $received_data,
                'total_grades'=>$total_grades,

                // 'default_grade'=>$default_grade,//arr, key is received method name
                // 'default_product_grade' => $default_product_grade,//str, value is grade id
                // 'default_logistics_grade' => $default_logistics_grade,//str, value is grade id
                // 'default_discount_grade' => $default_discount_grade,//arr, key is discount method name

                'breadcrumb_data' => ['id'=>$order->id, 'sn'=>$order->sn],
            ]);
        }
    }


    public function destroy($id)
    {
        $target = ReceivedOrder::delete_received_order($id);
        if($target){
            if($target->source_type == app(Order::class)->getTable()){
                OrderFlow::changeOrderStatus($target->source_id, OrderStatus::Add());
                $r_method['value'] = '';
                $r_method['description'] = '';
                Order::change_order_payment_status($target->source_id, PaymentStatus::Unpaid(), (object) $r_method);
            }

            wToast('刪除完成');

        } else {
            wToast('刪除失敗');
        }
        return redirect()->back();
    }
}