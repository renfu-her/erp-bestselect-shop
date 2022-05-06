<?php

namespace App\Http\Controllers\Cms\Accounting;

use App\Enums\Received\ReceivedMethod;
use App\Http\Controllers\Controller;
use App\Models\GeneralLedger;
use App\Models\ReceivedDefault;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $allReceivedMethod = ReceivedMethod::asArray();
        $queries = DB::table('acc_received_default')
            ->whereIn('name', $allReceivedMethod)
            //「外幣」另外會分類處理
            ->whereNotIn('name', [ReceivedMethod::ForeignCurrency])
            ->get();

        $defaultList = [];
        foreach ($queries as $query) {
            $defaultList[$query->name]['description'] = ReceivedMethod::getDescription($query->name);
            $defaultList[$query->name]['default_grade_id'][] = $query->default_grade_id;
        }
        foreach ($allReceivedMethod as $receivedMethod) {
            // 若使用者無設定「付款方式」，則補上[]數值，只在前端顯示「付款方式」文字，至於「外幣」另外分類處理
            if (!array_key_exists($receivedMethod, $defaultList) &&
                $receivedMethod !== ReceivedMethod::ForeignCurrency) {
                $defaultList[$receivedMethod]['description'] = ReceivedMethod::getDescription($receivedMethod);
                $defaultList[$receivedMethod]['default_grade_id'] = [];
            }
        }

        //sort defaultList by the design order of ReceivedMethod Enum
        $defaultArray = array_replace(array_flip(ReceivedMethod::asArray()), $defaultList);
        //「外幣」另外處理
        unset($defaultArray[ReceivedMethod::ForeignCurrency]);

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

        return view('cms.accounting.received_default.edit', [
            'totalGrades' => $totalGrades,
            'defaultArray' => $defaultArray,
            'currencyDefaultArray' => $currencyDefaultArray,
            'default_product_grade' => $default_product_grade,
            'default_logistics_grade' => $default_logistics_grade,
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
            ReceivedMethod::Cash . '.default_grade_id.*' => ['nullable', 'numeric', 'min:1'],
            ReceivedMethod::Cheque . '.default_grade_id.*' => ['nullable', 'numeric', 'min:1'],
            ReceivedMethod::CreditCard . '.default_grade_id.*' => ['nullable', 'numeric', 'min:1'],
            ReceivedMethod::Remittance . '.default_grade_id.*' => ['nullable', 'numeric', 'min:1'],
            ReceivedMethod::AccountsReceivable . '.default_grade_id.*' => ['nullable', 'numeric', 'min:1'],
            ReceivedMethod::Other . '.default_grade_id.*' => ['nullable', 'numeric', 'min:1'],
            ReceivedMethod::Refund . '.default_grade_id.*' => ['nullable', 'numeric', 'min:1'],

            ReceivedMethod::ForeignCurrency => ['required', 'array'],
            ReceivedMethod::ForeignCurrency . '.rate' => ['required', 'array'],
            //匯率計算到小數點第2位
            ReceivedMethod::ForeignCurrency . '.rate.*' => ['required', 'regex:/^\d+\.\d{2}$/', 'min:0'],

            //table acc_currency的Id array
            ReceivedMethod::ForeignCurrency . '.grade_id_fk' => ['nullable', 'array'],
            //若有回傳會計科目的id，不得小於1
            ReceivedMethod::ForeignCurrency . '.grade_id_fk.*' => ['required', 'int', 'min:1'],

            'orderDefault'=>'required|array:product,logistics',
            'orderDefault.product'=>'nullable|exists:acc_all_grades,id',
            'orderDefault.logistics'=>'nullable|exists:acc_all_grades,id',
        ]);

        $req = $request->all();

        ReceivedDefault::updateCurrency($req['foreign_currency']);
        ReceivedDefault::updateReceivedDefault($req);

        $default_product_grade = ReceivedDefault::where('name', 'product')->first();
        if($default_product_grade){
            ReceivedDefault::where('name', 'product')->update([
                'default_grade_id' => $req['orderDefault']['product'],
            ]);
        } else {
            ReceivedDefault::create([
                'name' => 'product',
                'default_grade_id' => $req['orderDefault']['product'],
            ]);
        }

        $default_logistics_grade = ReceivedDefault::where('name', 'logistics')->first();
        if($default_logistics_grade){
            ReceivedDefault::where('name', 'logistics')->update([
                'default_grade_id' => $req['orderDefault']['logistics'],
            ]);
        } else {
            ReceivedDefault::create([
                'name' => 'logistics',
                'default_grade_id' => $req['orderDefault']['logistics'],
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
