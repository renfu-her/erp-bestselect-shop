<?php

namespace App\Http\Controllers\Cms\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductSpec;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class SpecCtrl extends Controller
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
        $dataList =  ProductSpec::paginate($data_per_page)->appends($query);

        return view('cms.settings.spec.list', [
            'dataList' => $dataList,
            'data_per_page' => $data_per_page,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('cms.settings.spec.edit', [
            'method' => 'create',
            'formAction' => Route('cms.spec.create'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required',
                        'string',
                        'unique:App\Models\ProductSpec'
            ]
        ]);

        ProductSpec::create([
            'title' => $request->input('title')
        ]);
        return redirect(Route('cms.spec.index'));
    }
}
