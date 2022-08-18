<?php

namespace App\Http\Controllers\Cms\Settings;

use App\Http\Controllers\Controller;
use App\Models\CrdCreditCard;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class CreditCardCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $query = $request->query();
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;

        $crdCreditCard = CrdCreditCard::paginate($data_per_page)->appends($query);

        return view('cms.settings.credit_card.list', [
            'data_per_page' => $data_per_page,
            "dataList" => $crdCreditCard,
        ]);
    }

    public function create()
    {
        return view('cms.settings.credit_card.edit', [
            'method' => 'create',
            'formAction' => Route('cms.credit_card.create'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string']
        ]);

        $id = CrdCreditCard::create([
            'title' => $request->input('title')
        ]);
        return redirect(Route('cms.credit_card.index', ['id' => $id]));
    }

    public function edit(Request $request, int $id)
    {
        $data = CrdCreditCard::where('id', $id)->get()->first();
        return view('cms.settings.credit_card.edit', [
            'data' => $data,
            'method' => 'edit',
            'formAction' => Route('cms.credit_card.edit', ['id' => $id]),
        ]);
    }

    public function update(Request $request, int $id)
    {
        CrdCreditCard::where('id', $request->input('id'))
            ->update(['title' => $request->input('title')]);
        return redirect(Route('cms.credit_card.index', ['id' => $id]));
    }

    public function destroy(Request $request, int $id)
    {
        CrdCreditCard::where('id', '=', $id)->delete();
        wToast(__('Delete finished.'));
        return redirect(Route('cms.credit_card.index'));
    }
}
