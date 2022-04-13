<?php

namespace App\Http\Controllers\Cms\Frontend\Homepage;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\Collection;
use Illuminate\Http\Request;
class TemplateCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();
        $dataList = Template::getList()->orderBy('sort')->get();

        return view('cms.frontend.homepage.template.list', [
            'dataList' => $dataList,
            'formAction' => Route('cms.homepage.template.sort')
        ]);
    }

    public function sort(Request $request)
    {
        Template::sort($request);

        wToast(__('Edit finished.'));
        return redirect(Route('cms.homepage.template.index'));
    }

    public function create(Request $request)
    {
        return view('cms.frontend.homepage.template.edit', [
            'method' => 'create',
            'collectionList' => Collection::all(),
            'formAction' => Route('cms.homepage.template.create'),
        ]);
    }

    public function store(Request $request)
    {
        $query = $request->query();
        $templateID = Template::storeNew($request);
        wToast(__('Add finished.'));
        return redirect(Route('cms.homepage.template.index'));
    }
    public function edit(Request $request, $id)
    {
        $data = Template::where('id', '=', $id)->first();

        if (!$data) {
            return abort(404);
        }
        return view('cms.frontend.homepage.template.edit', [
            'id' => $id,
            'data' => $data,
            'method' => 'edit',
            'collectionList' => Collection::all(),
            'formAction' => Route('cms.homepage.template.edit', ['id' => $id]),
            'breadcrumb_data' => $id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $query = $request->query();
        Template::updateData($request, $id);
        wToast(__('Edit finished.'));
        return redirect(Route('cms.homepage.template.index'));
    }

    public function destroy($id)
    {
        Template::destroyById($id);
        wToast(__('Delete finished.'));
        return redirect(Route('cms.homepage.template.index'));
    }
}
