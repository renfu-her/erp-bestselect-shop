<?php

namespace App\Http\Controllers\Cms\Frontend\Homepage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TemplateCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();

        return view('cms.frontend.homepage.template.list', [
            'dataList' => [],
            'formAction' => Route('cms.homepage.template.sort')
        ]);
    }

    public function sort(Request $request)
    {
        // 
    }

    public function create(Request $request)
    {
        return view('cms.frontend.homepage.template.edit', [
            'method' => 'create',
            'formAction' => Route('cms.homepage.template.create'),
        ]);
    }
    
    public function edit(Request $request, $id)
    {
        return view('cms.frontend.homepage.template.edit', [
            'method' => 'edit',
            'id' => $id,
            'formAction' => Route('cms.homepage.template.edit', ['id' => $id]),
            'breadcrumb_data' => $id,
        ]);
    }

    public function destroy($id)
    {
        // 
    }
}
