<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Enums\Delivery\Event;
use App\Enums\Delivery\LogisticStatus;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\LogisticFlow;
use App\Models\SubOrders;
use Illuminate\Http\Request;

use App\Enums\Area\Area;
use App\Enums\Received\ReceivedMethod;
use App\Enums\Received\ChequeStatus;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use App\Models\AllGrade;
use App\Models\CrdCreditCard;
use App\Models\Customer;
use App\Models\GeneralLedger;
use App\Models\IncomeOrder;
use App\Models\OrderPayCreditCard;
use App\Models\Product;
use App\Models\ReceivedDefault;
use App\Models\ReceivedOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Depot;

abstract class AccountReceivedPapaCtrl extends Controller
{
    abstract public function getOrderData($order_id);
    abstract public function getOrderListData($order_id);
    abstract public function getOrderListItemMsg($Item);
    abstract public function getOrderPurchaser($order_data);
    abstract public function getSource_type();
    abstract public function getViewEdit();
    abstract public function getRouteStore();
    abstract public function getRouteCreate();
    abstract public function getRouteDetail();
    abstract public function getRouteReceipt();
    abstract public function getViewReceipt();
    abstract public function getRouteReview();
    abstract public function getViewReview();
    abstract public function getRouteTaxation();
    abstract public function getViewTaxation();

    abstract public function doDestroy($source_id);

    abstract public function doReviewWhenReceived($id);
    abstract public function doReviewWhenReceiptCancle($id);

    abstract public function doTaxationWhenGet();
    abstract public function doTaxationWhenUpdate();


    public function index(Request $request)
    {
        $query = $request->query();
        $page = getPageCount(Arr::get($query, 'data_per_page', 100)) > 0 ? getPageCount(Arr::get($query, 'data_per_page', 100)) : 100;

        $check_review_status = [
            'all'=>'不限',
            '0'=>'入款未審核',
            '1'=>'入款已審核',
        ];

        $cond = [];

        $cond['drawee_key'] = Arr::get($query, 'drawee_key', null);
        if (gettype($cond['drawee_key']) == 'string') {
            $key = explode('|', $cond['drawee_key']);
            $cond['drawee']['id'] = $key[0];
            $cond['drawee']['name'] = $key[1];
        } else {
            $cond['drawee'] = [];
        }

        $cond['ro_sn'] = Arr::get($query, 'ro_sn', null);
        $cond['source_sn'] = Arr::get($query, 'source_sn', null);

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
            $cond['drawee'],
            $cond['ro_sn'],
            $cond['source_sn'],
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
            if($value->received_list){
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
            }

            // 商品
            if($value->order_items){
                $product_account = AllGrade::find($value->ro_product_grade_id) ? AllGrade::find($value->ro_product_grade_id)->eachGrade : null;
                $account_code = $product_account ? $product_account->code : '4000';
                $account_name = $product_account ? $product_account->name : '無設定會計科目';
                $product_name = $account_code . ' ' . $account_name;
                foreach(json_decode($value->order_items) as $o_value){
                    $name = $product_name . ' --- ' . $o_value->product_title . '（' . $o_value->price . ' * ' . $o_value->qty . '）';
                    $product_title = $o_value->product_title;

                    if($value->ro_source_type == 'ord_received_orders' || $value->ro_source_type == 'acc_request_orders'){
                        $product_account = AllGrade::find($o_value->all_grades_id) ? AllGrade::find($o_value->all_grades_id)->eachGrade : null;
                        $account_code = $product_account ? $product_account->code : '4000';
                        $account_name = $product_account ? $product_account->name : '無設定會計科目';
                        $product_title = $account_name;
                    }

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
                        'product_title'=>$product_title,
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
            }

            // 物流
            if($value->order_dlv_fee <> 0){
                $log_account = AllGrade::find($value->ro_logistics_grade_id) ? AllGrade::find($value->ro_logistics_grade_id)->eachGrade : null;
                $account_code = $log_account ? $log_account->code : '4000';
                $account_name = $log_account ? $log_account->name : '無設定會計科目';
                $name = $account_code . ' ' . $account_name;

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
                    $name = $account_code . ' ' . $account_name;

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

            if($value->ro_source_type == 'ord_orders'){
                $value->ro_url_link = route('cms.collection_received.receipt', ['id' => $value->ro_source_id]);

            } else if($value->ro_source_type == 'csn_orders'){
                $value->ro_url_link = route('cms.ar_csnorder.receipt', ['id' => $value->ro_source_id]);

            } else if($value->ro_source_type == 'ord_received_orders'){
                $value->ro_url_link = route('cms.account_received.ro-receipt', ['id' => $value->ro_source_id]);

            } else if($value->ro_source_type == 'acc_request_orders'){
                $value->ro_url_link = route('cms.request.ro-receipt', ['id' => $value->ro_source_id]);

            } else {
                $value->ro_url_link = "javascript:void(0);";
            }
        }
        // accounting classification end

        $user = User::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $customer = Customer::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $depot = Depot::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $supplier = Supplier::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $drawee_merged = array_merge($user, $customer, $depot, $supplier);

        return view('cms.account_management.collection_received.list', [
            'data_per_page' => $page,
            'dataList' => $dataList,
            'cond' => $cond,
            'drawee' => $drawee_merged,
            'check_review_status' => $check_review_status,
        ]);
    }


    /**
     * 收款方式
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $id)
    {
        $order_id = request('id');
        $order_data = $this->getOrderData($order_id);

        $order_purchaser = $this->getOrderPurchaser($order_data);
        $order_list_data = $this->getOrderListData($order_id);

        $source_type = $this->getSource_type();
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

        $card_type = CrdCreditCard::distinct('title')->groupBy('title')->orderBy('id', 'asc')->pluck('title', 'id')->toArray();

        $checkout_area = Area::get_key_value();

        return view($this->getViewEdit(), [
            'defaultArray' => $defaultArray,
            'currencyDefaultArray' => $currencyDefaultArray,
            'tw_price' => $tw_price,
            'receivedMethods' => ReceivedMethod::asSelectArray(),
            'formAction' => Route($this->getRouteStore()),
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

    /**
     * 產生收款單,產生後不能修改收款單，只能刪除再重新產生
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:' . $this->getSource_type() . ',id',
            'acc_transact_type_fk' => 'required|string|in:' . implode(',', ReceivedMethod::asArray()),
            'tw_price' => 'required|numeric',
            request('acc_transact_type_fk') => 'required|array',
            request('acc_transact_type_fk') . '.grade' => 'required|exists:acc_all_grades,id',
            'summary'=>'nullable|string',
            'note'=>'nullable|string',
        ]);

        $data = $request->except('_token');
        $received_order_collection = ReceivedOrder::where([
            'source_type'=>$this->getSource_type(),
            'source_id'=>$data['id'],
        ]);
        $received_order_id = $received_order_collection->first()->id;

        DB::beginTransaction();

        try {
            // 'credit_card'
            if($data['acc_transact_type_fk'] == ReceivedMethod::CreditCard){
                $card_type = CrdCreditCard::distinct('title')->groupBy('title')->orderBy('id', 'asc')->pluck('title', 'id')->toArray();

                $checkout_area = Area::get_key_value();

                $data[$data['acc_transact_type_fk']] = [
                    'cardnumber'=>$data[$data['acc_transact_type_fk']]['cardnumber'],
                    'authamt'=>$data['tw_price'] ?? 0,
                    'checkout_date'=>$data[$data['acc_transact_type_fk']]['checkout_date'] ?? null,// date('Y-m-d H:i:s')
                    'card_type_code'=>$data[$data['acc_transact_type_fk']]['card_type_code'] ?? null,
                    'card_type'=>$card_type[$data[$data['acc_transact_type_fk']]['card_type_code']] ?? null,
                    'card_owner_name'=>$data[$data['acc_transact_type_fk']]['card_owner_name'] ?? null,
                    'authcode'=>$data[$data['acc_transact_type_fk']]['authcode'] ?? null,
                    'all_grades_id'=>$data[$data['acc_transact_type_fk']]['grade'],
                    'checkout_area_code'=>'taipei',// $data[$data['acc_transact_type_fk']]['credit_card_area_code']
                    'checkout_area'=>'台北',// $checkout_area[$data[$data['acc_transact_type_fk']]['credit_card_area_code']]
                    'installment'=>$data[$data['acc_transact_type_fk']]['installment'] ?? 'none',
                    'status_code'=>0,
                    'card_nat'=>'local',
                    'checkout_mode'=>'offline',
                ];

                $data[$data['acc_transact_type_fk']]['grade'] = $data[$data['acc_transact_type_fk']]['all_grades_id'];

                $EncArray['more_info'] = $data[$data['acc_transact_type_fk']];

            } else if($data['acc_transact_type_fk'] == ReceivedMethod::AccountsReceivable){
                //
            }

            $result_id = ReceivedOrder::store_received_method($data);

            $parm = [];
            $parm['received_order_id'] = $received_order_id;
            $parm['received_method'] = $data['acc_transact_type_fk'];
            $parm['received_method_id'] = $result_id;
            $parm['grade_id'] = $data[$data['acc_transact_type_fk']]['grade'];
            $parm['price'] = $data['tw_price'];
            // $parm['accountant_id_fk'] = auth('user')->user()->id;
            $parm['summary'] = $data['summary'];
            $parm['note'] = $data['note'];
            ReceivedOrder::store_received($parm);

            if($data['acc_transact_type_fk'] == ReceivedMethod::CreditCard){
                OrderPayCreditCard::create_log($this->getSource_type(), $data['id'], (object) $EncArray);
            }

            DB::commit();
            wToast(__('收款單儲存成功'));

        } catch (\Exception $e) {
            DB::rollback();
            wToast(__('收款單儲存失敗', ['type'=>'danger']));
        }


        if (ReceivedOrder::find($received_order_id) && ReceivedOrder::find($received_order_id)->balance_date) {
            return redirect()->route($this->getRouteDetail(), [
                'id' => $data['id'],
            ]);

        } else {
            return redirect()->route($this->getRouteCreate(), [
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

        $order = $this->getOrderData(request('id'));
        $received_order_collection = ReceivedOrder::where([
            'source_type'=>$this->getSource_type(),
            'source_id'=>$id,
        ]);

        $received_order_data = $received_order_collection->get();
        if (count($received_order_data) == 0 || !$received_order_collection->first()->balance_date) {
            return abort(404);
        }

        $order_list_data = $this->getOrderListData(request('id'));
        $product_qc = $order_list_data->pluck('product_user_name')->toArray();
        $product_qc = array_unique($product_qc);
        asort($product_qc);

        $received_data = ReceivedOrder::get_received_detail($received_order_data->pluck('id')->toArray());
        $data_status_check = false;
        foreach($received_data as $rd_value){
            if($rd_value->credit_card_status_code == 2 || $rd_value->cheque_status_code == 'cashed'){
                $data_status_check = true;
            }
        }

        $order_purchaser = $this->getOrderPurchaser($order);
        $undertaker = User::find($received_order_collection->first()->usr_users_id);

        // $accountant = User::whereIn('id', $received_data->pluck('accountant_id_fk')->toArray())->get();
        // $accountant = array_unique($accountant->pluck('name')->toArray());
        // asort($accountant);
        $accountant = User::find($received_order_collection->first()->accountant_id) ? User::find($received_order_collection->first()->accountant_id)->name : null;

        $product_grade_name = AllGrade::find($received_order_collection->first()->product_grade_id)->eachGrade->code . ' ' . AllGrade::find($received_order_collection->first()->product_grade_id)->eachGrade->name;

        $logistics_grade = AllGrade::find($received_order_collection->first()->logistics_grade_id);
        if (isset($logistics_grade)) {
            $logistics_grade_name = $logistics_grade->eachGrade->code . ' '. $logistics_grade->eachGrade->name;
        }

        $order_discount = DB::table('ord_discounts')->where([
            'order_type'=>'main',
            'order_id'=>request('id'),
        ])->where('discount_value', '>', 0)->get()->toArray();

        foreach($order_discount as $value){
            $value->account_code = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->code : '4000';
            $value->account_name = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->name : '無設定會計科目';
        }

        $zh_price = num_to_str($received_order_collection->first()->price);

        return view($this->getViewReceipt(), [
            'breadcrumb_data' => ['id'=>$order->id, 'sn'=>$order->sn],

            'received_order'=>$received_order_collection->first(),
            'order'=>$order,
            'order_discount'=>$order_discount,
            'order_list_data' => $order_list_data,
            'received_data' => $received_data,
            'data_status_check' => $data_status_check,
            'order_purchaser' => $order_purchaser,
            'undertaker'=>$undertaker,
            'product_qc'=>implode(',', $product_qc),
            // 'accountant'=>implode(',', $accountant),
            'accountant'=>$accountant,
            'product_grade_name'=>$product_grade_name,
            'logistics_grade_name'=>$logistics_grade_name ?? '',
            'zh_price' => $zh_price,
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
            'source_type'=>$this->getSource_type(),
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

            DB::beginTransaction();

            try {
                $received_order->update([
                    'accountant_id'=>auth('user')->user()->id,
                    'receipt_date'=>request('receipt_date'),
                    'invoice_number'=>request('invoice_number'),
                ]);

                if(is_array(request('received_method'))){
                    $unique_m = array_unique(request('received_method'));

                    foreach($unique_m as $m_value){
                        if( in_array($m_value, ReceivedMethod::asArray()) && is_array(request($m_value))){
                            $req = request($m_value);
                            foreach($req as $r){
                                $r['received_method'] = $m_value;
                                ReceivedOrder::update_received_method($r);
                            }
                        }
                    }
                }

                $this->doReviewWhenReceived($id);
	            //修改子訂單物流配送狀態為檢貨中
	            $sub_orders = SubOrders::where('order_id', '=', $id)->get();
	            if (isset($sub_orders) && 0 < count($sub_orders)) {
	                $sub_order_ids = [];
	                foreach ($sub_orders as $sub_order) {
	                    array_push($sub_order_ids, $sub_order->id);
	                }
	                $delivery = Delivery::whereIn('event_id', $sub_order_ids)->where('event', '=', Event::order()->value)->get();
	                if (isset($delivery) && 0 < count($delivery)) {
	                    foreach ($delivery as $dlv) {
	                        $reLFCDS = LogisticFlow::createDeliveryStatus($request->user(), $dlv->id, [LogisticStatus::A2000()]);
                            if ($reLFCDS['success'] == 0) {
                                DB::rollBack();
                                return $reLFCDS;
                            }
	                    }
	                }
	            }

                DB::commit();
                wToast(__('入帳日期更新成功'));

                return redirect()->route($this->getRouteReceipt(), ['id'=>request('id')]);

            } catch (\Exception $e) {
                DB::rollback();
                wToast(__('入帳日期更新失敗', ['type'=>'danger']));

                return redirect()->back();
            }

        } else if($request->isMethod('get')){
            $received_data = ReceivedOrder::get_received_detail($received_order_data->pluck('id')->toArray());
            $data_status_check = false;
            foreach($received_data as $rd_value){
                if($rd_value->credit_card_status_code == 2 || $rd_value->cheque_status_code == 'cashed'){
                    $data_status_check = true;
                }
            }

            if($received_order->receipt_date){
                if($data_status_check){
                    return redirect()->back();
                }

                $received_order->update([
                    'accountant_id'=>null,
                    'receipt_date'=>null,
                ]);

                $this->doReviewWhenReceiptCancle($id);
                wToast(__('入帳日期已取消'));
                return redirect()->route($this->getRouteReceipt(), ['id'=>request('id')]);

            } else {
                $undertaker = User::find($received_order->usr_users_id);
                $order = $this->getOrderData(request('id'));

                $order_list_data = $this->getOrderListData(request('id'));

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
                $product_grade_name = $account_code . ' ' . $account_name;
                foreach($order_list_data as $value){
                    $name = $product_grade_name . ' --- ' . $this->getOrderListItemMsg($value);
                    // GeneralLedger::classification_processing($debit, $credit, $product_master_account->code, $name, $value->product_origin_price, 'r', 'product');

                    $tmp = [
                        'account_code'=>$account_code,
                        'name'=>$name,
                        'price'=>$value->product_origin_price,
                        'type'=>'r',
                        'd_type'=>'product',

                        'account_name'=>$account_name,
                        'method_name'=>null,
                        'summary'=>$value->summary ?? null,
                        'note'=>$value->note ?? null,
                        'product_title'=>$value->product_title,
                        'del_even'=>$value->del_even ?? null,
                        'del_category_name'=>$value->del_category_name ?? null,
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
                    // $name = $logistics_grade_name = $account_code . ' ' . $account_name;
                    $name = $account_code . ' ' . $account_name;
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
                        $name = $account_code . ' ' . $account_name . ' - ' . $value->title;
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

                $card_type = CrdCreditCard::distinct('title')->groupBy('title')->orderBy('id', 'asc')->pluck('title', 'id')->toArray();

                $checkout_area = Area::get_key_value();

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

                $cheque_status = [];
                foreach (ChequeStatus::asArray() as $data) {
                    $cheque_status[$data] = ChequeStatus::getDescription($data);
                }

                return view($this->getViewReview(), [
                    'form_action'=>route($this->getRouteReview() , ['id'=>request('id')]),
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
                    'cheque_status'=>$cheque_status,
                    'credit_card_grade'=>$default_grade[ReceivedMethod::CreditCard],
                    'cheque_grade'=>$default_grade[ReceivedMethod::Cheque],
                    // 'default_grade'=>$default_grade,
                    // 'currency_default_grade'=>$currency_default_grade,

                    'breadcrumb_data' => ['id'=>$order->id, 'sn'=>$order->sn],
                ]);
            }
        }
    }

    public function taxation(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);
        $request->validate([
            'id' => 'required|exists:ord_orders,id',
        ]);

        $received_order_collection = ReceivedOrder::where([
            'source_type'=>$this->getSource_type(),
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

                $this->doTaxationWhenUpdate();

                DB::commit();
                wToast(__('摘要/稅別更新成功'));

            } catch (\Exception $e) {
                DB::rollback();
                wToast(__('摘要/稅別更新失敗', ['type'=>'danger']));
            }

            return redirect()->route($this->getRouteReceipt(), ['id'=>request('id')]);

        } else if($request->isMethod('get')){

            $order = $this->getOrderData(request('id'));
            $order_list_data = $this->getOrderListData(request('id'));
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

            list($discount_type, $default_discount_grade) = $this->doTaxationWhenGet();
            // grade process end

            return view($this->getViewTaxation(), [
                'form_action'=>route($this->getRouteTaxation() , ['id'=>request('id')]),
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
            if($target->source_type == $this->getSource_type()){
                $this->doDestroy($target->source_id);
            }

            // credit card - income order record update
            $r_method_list = ReceivedOrder::get_received_detail($id, ReceivedMethod::CreditCard)->where('credit_card_status_code', 2)->groupBy('credit_card_io_id');
            foreach($r_method_list as $group){
                foreach($group as $data){
                    $parm = [
                        'credit_card_received_id'=>[$data->received_method_id],
                        'status_code'=>1,
                        'transaction_date'=>$data->credit_card_transaction_date,
                    ];
                    ReceivedOrder::update_credit_received_method($parm);
                }

                IncomeOrder::store_income_order($group->first()->credit_card_posting_date);
            }


            wToast('刪除完成');

        } else {
            wToast('刪除失敗', ['type'=>'danger']);
        }
        return redirect()->back();
    }
}

