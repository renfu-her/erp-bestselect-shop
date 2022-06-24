<?php

namespace App\Http\Controllers\Cms\Settings;

use App\Http\Controllers\Controller;
use App\Models\CrdCreditCard;
use App\Models\CrdPercentBankCredit;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class CreditPercentCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $query = $request->query();

        $keyword_bank = Arr::get($query, 'keyword_bank', null);
        $keyword_credit_id = Arr::get($query, 'keyword_credit_id', null);
        $arr = CrdPercentBankCredit::getList($keyword_bank, $keyword_credit_id);

        return view('cms.settings.credit_percent.list', [
            "keyword_bank" => $keyword_bank,
            "keyword_credit_id" => $keyword_credit_id,
            "cards" => CrdCreditCard::all(),
            "dataList" => $arr,
        ]);
    }

    public function edit(Request $request, int $bank_id, int $card_id)
    {
        $precentBC = CrdPercentBankCredit::where('bank_fk', $bank_id)->where('credit_fk', $card_id)->get()->first();
        if (null == $precentBC) {
            $data_id = CrdPercentBankCredit::create([
                'bank_fk' => $bank_id,
                'credit_fk' => $card_id,
            ])->id;
            $precentBC = CrdPercentBankCredit::where('id', $data_id)->get()->first();
        }

        $detailData = CrdPercentBankCredit::getDetailData($precentBC->id)->get()->first();

        return view('cms.settings.credit_percent.edit', [
            'data' => $detailData,
            'method' => 'edit',
            'formAction' => Route('cms.credit_percent.edit', ['bank_id' => $bank_id, 'credit_id' => $card_id]),
        ]);
    }

    public function update(Request $request, int $bank_id, int $card_id)
    {
        $request->validate([
            'id' => ['required', 'numeric', 'min:1'],
            'percent' => ['required', 'numeric', 'min:0'],
        ]);
        $input = $request->only('id', 'percent');
        CrdPercentBankCredit::where('id', $input['id'])
            ->update([
                'percent' => $input['percent'],
            ]);
        wToast(__('Edit finished.'));
        return redirect(Route('cms.credit_percent.edit', ['bank_id' => $bank_id, 'credit_id' => $card_id]));
    }
}
