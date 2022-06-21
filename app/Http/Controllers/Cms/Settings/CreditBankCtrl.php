<?php

namespace App\Http\Controllers\Cms\Settings;

use App\Http\Controllers\Controller;
use App\Models\CrdBank;
use App\Models\GeneralLedger;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreditBankCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $query = $request->query();
        $data_per_page = Arr::get($query, 'data_per_page', 100);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 100;

        $keyword = Arr::get($query, 'keyword', null);

        $crdCreditBank = DB::table('crd_banks')
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'grade', function($join) {
                $join->on('grade.primary_id', 'crd_banks.grade_fk');
            })
            ->select(
                'crd_banks.id'
                , 'crd_banks.title'
                , 'grade.code'
                , 'grade.name'
            );
        if (isset($keyword)) {
            $crdCreditBank->where(function ($q) use ($keyword) {
                if ($keyword) {
                    $q->where('grade.name', 'like', "%$keyword%");
                }
            });
        }
        $crdCreditBank = $crdCreditBank->paginate($data_per_page)->appends($query);

        return view('cms.settings.credit_bank.list', [
            'data_per_page' => $data_per_page,
            "dataList" => $crdCreditBank,
            'formAction' => Route('cms.credit_bank.index'),
        ]);
    }

    public function create()
    {
        $total_grades = GeneralLedger::total_grade_list();
        return view('cms.settings.credit_bank.edit', [
            'method' => 'create',
            'total_grades' => $total_grades,
            'formAction' => Route('cms.credit_bank.create'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string'],
            'bank_code' => ['nullable', 'string'],
            'grade_id' => ['required', 'numeric', 'min:1']
        ]);

        $input = $request->only('title', 'bank_code', 'grade_id');

        $id = CrdBank::create([
            'title' => $input['title'],
            'bank_code' => $input['bank_code'],
            'grade_fk' => $input['grade_id'],
        ]);
        return redirect(Route('cms.credit_bank.edit', ['id' => $id]));
    }

    public function edit(Request $request, int $id)
    {
        $data = CrdBank::where('id', $id)->get()->first();
        $total_grades = GeneralLedger::total_grade_list();

        return view('cms.settings.credit_bank.edit', [
            'data' => $data,
            'total_grades' => $total_grades,
            'method' => 'edit',
            'formAction' => Route('cms.credit_bank.edit', ['id' => $id]),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'title' => ['required', 'string'],
            'bank_code' => ['nullable', 'string'],
            'grade_id' => ['required', 'numeric', 'min:1']
        ]);
        $input = $request->only('title', 'bank_code', 'grade_id');
        CrdBank::where('id', $request->input('id'))
            ->update([
                'title' => $input['title'],
                'bank_code' => $input['bank_code'],
                'grade_fk' => $input['grade_id'],
            ]);
        return redirect(Route('cms.credit_bank.edit', ['id' => $id]));
    }

    public function destroy(Request $request, int $id)
    {
        CrdBank::where('id', '=', $id)->delete();
        wToast(__('Delete finished.'));
        return redirect(Route('cms.credit_bank.index'));
    }
}
