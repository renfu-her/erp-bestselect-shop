<?php

namespace App\Http\Controllers\Cms\Accounting;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use App\Enums\Discount\DisCategory;
use App\Enums\Received\ReceivedMethod;

use App\Models\Discount;
use App\Models\GeneralLedger;
use App\Models\ReceivedDefault;

class ReceivedDefaultCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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

        $all_received_method = ReceivedMethod::asArray();
        $received_method = [];
        foreach($all_received_method as $d_r_m_value){
            if($d_r_m_value != 'foreign_currency'){
                $received_method[$d_r_m_value] = ReceivedMethod::getDescription($d_r_m_value);
            }
        }

        $default_received_grade = [];
        foreach($received_method as $key => $value){
            $default_received_grade[$key] = ReceivedDefault::where('name', $key)->pluck('default_grade_id')->toArray();
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
                $currencyDefaultArray[ReceivedMethod::ForeignCurrency][] = [
                    'currency_id'    => $default->currency_id,
                    'currency_name'    => $default->currency_name,
                    'rate'             => $default->rate,
                    'default_grade_id' => $default->default_grade_id ?? null,
                ];
        }

        $default_product_grade = ReceivedDefault::where('name', 'product')->first() ? ReceivedDefault::where('name', 'product')->first()->default_grade_id : null;
        $default_logistics_grade = ReceivedDefault::where('name', 'logistics')->first() ? ReceivedDefault::where('name', 'logistics')->first()->default_grade_id : null;

        // $discount_type = Discount::whereNull('deleted_at')->distinct('category_code')->orderBY('category_code', 'ASC')->pluck('category_title', 'category_code')->toArray();
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

        return view('cms.accounting.received_default.edit', [
            'totalGrades' => $totalGrades,

            'received_method' => $received_method,
            'default_received_grade' => $default_received_grade,

            'currencyDefaultArray' => $currencyDefaultArray,

            'default_product_grade' => $default_product_grade,
            'default_logistics_grade' => $default_logistics_grade,

            'discount_type' => $discount_type,
            'default_discount_grade' => $default_discount_grade,

            'isViewMode' => true,
            'formAction' => Route('cms.received_default.edit', [], true),
            'formMethod' => 'post'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReceivedDefault  $receivedDefault
     * @return \Illuminate\Http\Response
     */
    public function show(ReceivedDefault $receivedDefault)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ReceivedDefault  $receivedDefault
     * @return \Illuminate\Http\Response
     */
    public function edit(ReceivedDefault $receivedDefault)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        //form的元素名稱用Enum ReceivedMethod命名
        $request->validate([
            ReceivedMethod::Cash => ['nullable', 'array'],
            ReceivedMethod::Cheque => ['nullable', 'array'],
            ReceivedMethod::CreditCard => ['nullable', 'array'],
            ReceivedMethod::Remittance => ['nullable', 'array'],
            ReceivedMethod::AccountsReceivable => ['nullable', 'array'],
            ReceivedMethod::Other => ['nullable', 'array'],
            ReceivedMethod::Refund => ['nullable', 'array'],

            //若有回傳會計科目的id，不得小於1
            ReceivedMethod::Cash . '.*' => ['nullable', 'numeric', 'min:1'],
            ReceivedMethod::Cheque . '.*' => ['nullable', 'numeric', 'min:1'],
            ReceivedMethod::CreditCard . '.*' => ['nullable', 'numeric', 'min:1'],
            ReceivedMethod::Remittance . '.*' => ['nullable', 'numeric', 'min:1'],
            ReceivedMethod::AccountsReceivable . '.*' => ['nullable', 'numeric', 'min:1'],
            ReceivedMethod::Other . '.*' => ['nullable', 'numeric', 'min:1'],
            ReceivedMethod::Refund . '.*' => ['nullable', 'numeric', 'min:1'],

            ReceivedMethod::ForeignCurrency => ['required', 'array'],
            ReceivedMethod::ForeignCurrency . '.rate' => ['required', 'array'],
            //匯率計算到小數點第2位
            ReceivedMethod::ForeignCurrency . '.rate.*' => ['required', 'regex:/^\d+\.\d{2}$/', 'min:0'],

            //table acc_currency的Id array
            ReceivedMethod::ForeignCurrency . '.grade_id_fk' => ['nullable', 'array'],
            //若有回傳會計科目的id，不得小於1
            ReceivedMethod::ForeignCurrency . '.grade_id_fk.*' => ['required', 'int', 'min:1'],

            'product'=>'required|exists:acc_all_grades,id',
            'logistics'=>'required|exists:acc_all_grades,id',
        ]);


        $discount_category = DisCategory::asArray();
        ksort($discount_category);

        foreach($discount_category as $dis_value){
            $request->validate([
                $dis_value=>'required|exists:acc_all_grades,id',
            ]);

            $query = ReceivedDefault::where('name', $dis_value)->first();
            if($query){
                $query->update([
                    'default_grade_id' => request($dis_value),
                ]);

            } else {
                ReceivedDefault::create([
                    'name' => $dis_value,
                    'default_grade_id' => request($dis_value),
                ]);
            }
        }

        $req = $request->all();

        ReceivedDefault::updateCurrency($req['foreign_currency']);
        ReceivedDefault::updateReceivedDefault($req);

        $default_product_grade = ReceivedDefault::where('name', 'product')->first();
        if($default_product_grade){
            ReceivedDefault::where('name', 'product')->update([
                'default_grade_id' => $req['product'],
            ]);
        } else {
            ReceivedDefault::create([
                'name' => 'product',
                'default_grade_id' => $req['product'],
            ]);
        }

        $default_logistics_grade = ReceivedDefault::where('name', 'logistics')->first();
        if($default_logistics_grade){
            ReceivedDefault::where('name', 'logistics')->update([
                'default_grade_id' => $req['logistics'],
            ]);
        } else {
            ReceivedDefault::create([
                'name' => 'logistics',
                'default_grade_id' => $req['logistics'],
            ]);
        }

        return redirect()->route('cms.received_default.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReceivedDefault  $receivedDefault
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReceivedDefault $receivedDefault)
    {
        //
    }
}
