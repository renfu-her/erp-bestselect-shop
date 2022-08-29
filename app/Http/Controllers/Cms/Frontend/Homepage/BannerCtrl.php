<?php

namespace App\Http\Controllers\Cms\Frontend\Homepage;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;

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

        wToast(__('Edit finished.'));
        return redirect(Route('cms.homepage.banner.index'));
    }

    public function create(Request $request)
    {
        $productList = Product::productList(null, null, ['public' => 1]);

        return view('cms.frontend.homepage.banner.edit', [
            'method' => 'create',
            'collectionList' => Collection::all(),
            'productList' => $productList->get(),
            'formAction' => Route('cms.homepage.banner.create'),
        ]);
    }

    public function store(Request $request)
    {
        $query = $request->query();
        $bannerID = Banner::storeNewBanner($request);
        wToast(__('Add finished.'));
        return redirect(Route('cms.homepage.banner.index'));
    }

    public function edit(Request $request, $id)
    {
        $data = Banner::where('id', '=', $id)->first();
        $productList = Product::productList();

        if (!$data) {
            return abort(404);
        }
        return view('cms.frontend.homepage.banner.edit', [
            'id' => $id,
            'data' => $data,
            'method' => 'edit',
            'collectionList' => Collection::all(),
            'productList' => $productList->get(),
            'formAction' => Route('cms.homepage.banner.edit', ['id' => $id]),
            'breadcrumb_data' => $id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $query = $request->query();
        $is_del_old_img = false;
        if ('del' == $request->input('del_img_pc')) {
            $is_del_old_img = true;
        }
        Banner::updateBanner($request, $id, $is_del_old_img);
        wToast(__('Edit finished.'));
        return redirect(Route('cms.homepage.banner.index'));
    }

    public function destroy($id)
    {
        Banner::destroyById($id);
        wToast(__('Delete finished.'));
        return redirect(Route('cms.homepage.banner.index'));
    }
}
