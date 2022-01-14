<?php

namespace App\Http\Controllers\Cms\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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
        $dataList = $collection->paginate(10)->appends($query);
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;

        return view('cms.frontend.collection.list', [
            'dataList' => $dataList,
            'data_per_page' => $data_per_page
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
            'collection_name'  => [
                'required',
                'string',
                'unique:App\Models\Collection,name'
            ],
            'url'              => 'nullable|string',
            'meta_title'       => 'nullable|string',
            'meta_description' => 'nullable|string',
            'is_public'        => 'bool',
            'id.*'             => 'required|int|min:0'
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
            $req['id']);

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
     * @return \Illuminate\Http\Response
     */
    public function edit(Collection $collection)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Collection  $collection
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Collection $collection)
    {
        //
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
}
