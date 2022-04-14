<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Enums\Accounting\GradeModelClass;
use App\Enums\Received\ReceivedMethod;
use App\Http\Controllers\Controller;
use App\Models\AccountReceived;
use App\Models\AllGrade;
use App\Models\GeneralLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
    public function create(Request $request)
    {
        $req = $request->all();
        $receivedMethods = ReceivedMethod::asSelectArray();
        $totalPrice = DB::table('ord_orders')->where('id', '=', $req['id']['ord_orders'])->value('total_price');

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
//        $allGrade = AllGrade::all();
//        $gradeModelArray = GradeModelClass::asSelectArray();

//        foreach ($allGrade as $grade) {
//            $allGradeArray[$grade->id] = [
//                'grade_id' => $grade->id,
//                'grade_num' => array_keys($gradeModelArray, $grade->grade_type)[0],
//                'code' => $grade->eachGrade->code,
//                'name' => $grade->eachGrade->name,
//            ];
//        }

        foreach ($totalGrades as $grade) {
            $allGradeArray[$grade['primary_id']] = $grade;
        }
        $defaultArray = [];
        foreach ($defaultData as $recMethod => $ids) {
            //收款方式若沒有預設、或是方式為「其它」，則自動帶入所有會計科目
            if ($ids !== true &&
                $recMethod !== 'other') {
                foreach ($ids as $id) {
                    $defaultArray[$recMethod][$id->default_grade_id] = [
//                        'methodName' => $recMethod,
                        'method' => ReceivedMethod::getDescription($recMethod),
                        'grade_id' => $id->default_grade_id,
                        'grade_num' => $allGradeArray[$id->default_grade_id]['grade_num'],
                        'code' => $allGradeArray[$id->default_grade_id]['code'],
                        'name' => $allGradeArray[$id->default_grade_id]['name'],
                    ];
                }
            } else {
                $defaultArray[$recMethod] = $allGradeArray;
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
            'tw_price' => $totalPrice,
            'receivedMethods' => $receivedMethods,
            'formAction' => Route('cms.ar.store'),
            'ord_orders_id' => $req['id']['ord_orders']
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

        $req = $request->all();
        $orderId = $req['id']['ord_orders'];

        //申請公司（系統先預設「喜鴻國際」）
        $appliedCompanyData = DB::table('acc_company')
                                ->where('id', '=', 1)
                                ->get()
                                ->first();

        $queries = DB::table('ord_orders as order')
                        ->where('order.id', '=', $orderId)
                        ->leftJoin('ord_address', function ($join) {
                            $join->on('ord_address.order_id', '=', 'order.id')
                                ->where('ord_address.type', '=', 'orderer');
                        })
                        ->leftJoin('ord_items as product', 'product.order_id', '=', 'order.id')
                        ->leftJoin('ord_sub_orders', function ($join) {
                            $join->on('ord_sub_orders.id', '=', 'product.sub_order_id');
                        })
                        ->leftJoin('prd_product_styles', 'product.product_style_id', '=', 'prd_product_styles.id')
                        ->leftJoin('prd_products', 'prd_product_styles.product_id', '=', 'prd_products.id')
                        ->leftJoin('usr_users', 'prd_products.user_id', '=', 'usr_users.id')
                        ->select(
                            'ord_address.name',
                            'ord_address.phone',
                            'ord_address.address',
                            'order.id',
                            'order.email',
                            'order.note',
                            'order.total_price',
                            'ord_sub_orders.sn as sub_sn',
                            'product.product_title',
                            'product.product_style_id',
                            'product.price',
                            'product.qty',
                            'product.total_price as prd_total_price',
                            'usr_users.name as product_owner'
                        )
                        ->get();

        $customer = $queries->first();

        $deliveries = DB::table('ord_sub_orders')
                            ->where('order_id', '=', $orderId)
                            ->where('dlv_fee', '<>', 0)
                            ->select('sn as sub_sn', 'dlv_fee')
                            ->get();

        $productOwnerArray = [];
        foreach ($queries as $query) {
            $productOwnerArray[] = $query->product_owner;
        }
        $productOwners = implode(',', array_unique($productOwnerArray));

        $underTaker = DB::table('usr_users')
                        ->where('id', '=', Auth::user()->id)
                        ->value('name');


        return view('cms.account_management.account_received.receipt', [
           'hasReviewed' => false,
           'appliedCompanyData' => $appliedCompanyData,
           'customer' => $customer,
           'products' => $queries,
           'deliveries' => $deliveries,
           'productOwners' => $productOwners,
           'underTaker' => $underTaker,
           'ord_orders_id' => $orderId,
        ]);
    }

    /**
     * 查看「收款單」
     *
     * @param  \App\Models\AccountReceived  $accountReceived
     * @return \Illuminate\Http\Response
     */
    public function show(AccountReceived $accountReceived)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AccountReceived  $accountReceived
     * @return \Illuminate\Http\Response
     */
    public function edit(AccountReceived $accountReceived)
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
    public function update(Request $request, AccountReceived $accountReceived)
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AccountReceived  $accountReceived
     * @return \Illuminate\Http\Response
     */
    public function destroy(AccountReceived $accountReceived)
    {
        //
    }
}
