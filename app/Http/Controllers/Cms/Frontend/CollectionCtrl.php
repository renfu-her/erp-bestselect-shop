<?php

namespace App\Http\Controllers\Cms\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CollectionCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Collection $collection)
    {

        $query = $request->query();
        $dataList = $collection->getDataList($query);
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;

        return view('cms.frontend.collection.list', [
            'dataList' => $dataList,
            'data_per_page' => $data_per_page,
            'topList' => Collection::where('is_liquor', 0)->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('cms.frontend.collection.edit', [
            'method' => 'create',
            'formAction' => Route('cms.collection.create'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Collection $collection)
    {
       
        $request->validate([
            'collection_name' => [
                'required',
                'string',
                'unique:App\Models\Collection,name',
            ],
            'url' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'is_public' => 'bool',
            'is_liquor' => ['required', 'int', 'min:0', 'max:1'],
            'id.*' => 'required|int|min:0',
            'sort.*' => 'required|int',
        ]);

        $req = $request->all();
        if (!isset($req['url'])) {
            $req['url'] = $req['collection_name'];
        }

        $collection->storeCollectionData(
            $req['collection_name'],
            $req['url'],
            $req['meta_title'],
            $req['meta_description'],
            false,
            $req['is_liquor'],
            $req['id'],
            $req['sort']);

        return redirect(Route('cms.collection.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Collection  $collection
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Collection $collection)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Collection  $collection
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Collection $collection, int $id)
    {   

        $dataList = $collection->getCollectionDataById($id);

        return view('cms.frontend.collection.edit', [
            'dataList' => $dataList,
            'method' => 'edit',
            'formAction' => Route('cms.collection.edit', $id),
            'collectionData' => $dataList->first(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Collection  $collection
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Collection $collection, int $id)
    {
        $ignoreId = $collection->where('id', $id)
            ->get()
            ->first()
            ->id;

        $request->validate([
            'collection_name' => [
                'required',
                'string',
                Rule::unique('collection', 'name')->ignore($ignoreId)],
            'url' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'is_liquor' => 'required|int|min:0',
            'id.*' => 'required|int|min:0',
            'sort.*' => 'required|int',
        ]);

       
        $req = $request->all();
        if (!isset($req['url'])) {
            $req['url'] = $req['collection_name'];
        }
        if (!isset($req['meta_title'])) {
            $req['meta_title'] = '';
        }
        if (!isset($req['meta_description'])) {
            $req['meta_description'] = '';
        }

        $collection->updateCollectionData(
            $id,
            $req['collection_name'],
            $req['url'],
            $req['meta_title'],
            $req['meta_description'],
            $req['is_liquor'],
            $req['id'],
            $req['sort'],
        );

        return redirect(Route('cms.collection.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Collection  $collection
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Collection $collection, int $id)
    {
        $collection->deleteCollectionById($id);

        return redirect(Route('cms.collection.index'));
    }

    public function publish(Request $request, Collection $collection)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|int|min:0',
        ]);

        $re = $request->all();

        $collection->changePublicStatus($re['id']);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'errors' => $validator->errors()], 400);
        }

        return response()->json(['status' => 'success']);
//        return redirect(Route('cms.collection.index'));
    }

    public function setErpTop(Request $request)
    {
        $request->validate([
            'top_id' => 'array|nullable',
        ]);

        $top_id = $request->input('top_id');
        Collection::query()->update(['erp_top' => 0]);
        if ($top_id) {

            Collection::whereIn('id', $top_id)->update(['erp_top' => 1]);

        }
        wToast('設定完成');

        return redirect()->back();
    }
}
