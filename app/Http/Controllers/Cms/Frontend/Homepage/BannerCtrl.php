<?php

namespace App\Http\Controllers\Cms\Frontend\Homepage;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BannerCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();
        $dataList = Banner::getList()->orderBy('sort')->get();

        return view('cms.frontend.homepage.banner.list', [
            'dataList' => $dataList
            , 'formAction' => Route('cms.homepage.banner.sort'),
        ]);
    }

    public function sort(Request $request)
    {
        Banner::sort($request);
        $req_banner_ids = $request->input('banner_id');
        if (isset($req_banner_ids) && 0 < count($req_banner_ids)) {
            $banner_ids = implode(',', $req_banner_ids);
            $condtion = '';
            foreach ($req_banner_ids as $sort => $id) {
                $condtion = $condtion. ' when '. $id. ' then '. $sort;
            }
            DB::update('update idx_banner set sort = case id'
                . $condtion
                . ' end where id in ('. $banner_ids. ')'
            );
        }

        wToast(__('Edit finished.'));
        return redirect(Route('cms.homepage.banner.index'));
    }

    public function create(Request $request)
    {
        $collectionList = Collection::all();
        return view('cms.frontend.homepage.banner.edit', [
            'method' => 'create',
            'collectionList' => Collection::all(),
            'formAction' => Route('cms.homepage.banner.create'),
        ]);
    }

    public function store(Request $request)
    {
        $query = $request->query();
        $bannerID = Banner::storeNewBanner($request);
        wToast(__('Add finished.'));
        return redirect(Route('cms.homepage.banner.create', [
            'id' => $bannerID,
            'query' => $query,
        ]));
    }

    public function edit(Request $request, $id)
    {
        $data = Banner::where('id', '=', $id)->first();

        if (!$data) {
            return abort(404);
        }
        return view('cms.frontend.homepage.banner.edit', [
            'id' => $id,
            'data' => $data,
            'method' => 'edit',
            'formAction' => Route('cms.homepage.banner.edit', ['id' => $id]),
            'breadcrumb_data' => $id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $query = $request->query();
        Banner::updateBanner($request, $id);
        wToast(__('Edit finished.'));
        return redirect(Route('cms.homepage.banner.edit', [
            'id' => $id,
            'query' => $query,
        ]));
    }

    public function destroy($id)
    {
        Banner::destroyById($id);
        wToast(__('Delete finished.'));
        return redirect(Route('cms.homepage.banner.index'));
    }
}
