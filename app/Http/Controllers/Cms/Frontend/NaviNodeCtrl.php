<?php

namespace App\Http\Controllers\Cms\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\NaviNode;
use Illuminate\Http\Request;

class NaviNodeCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $level = 0)
    {
        $parent_id = 0;
        $prev = null;
        if ($level) {
            $match = [];
            preg_match("/-(\d{1,})$/", $level, $match);

            if (!isset($match[1])) {
                return abort(404);
            }
            $parent_id = $match[1];
            $prev = preg_replace("/-(\d{1,})$/", "", $level);
        }

        return view('cms.frontend.navinode.list', [
            'dataList' => NaviNode::nodeList($parent_id)->get()->toArray(),
            'level' => $level,
            'prev' => $prev,
            'breadcrumb_data' => NaviNode::forBreadcrumb($level),
        ],
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create(Request $request, Collection $collection, $level = 0)
    {
        return view('cms.frontend.navinode.edit', [
            'method' => 'create',
            'formAction' => Route('cms.navinode.create', ['level' => $level]),
            'collections' => $collection->get()->toArray(),
            'breadcrumb_data' => ['level' => NaviNode::forBreadcrumb($level)],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $level = 0)
    {

        $parent_id = 0;
        if ($level) {
            $match = [];
            preg_match("/-(\d{1,})$/", $level, $match);
            if (!isset($match[1])) {
                return abort(404);
            }
            $parent_id = $match[1];
        }

        $request->validate([
            'title' => ['required', 'string'],
            'has_child' => 'required|in:0,1',
        ]);

        $d = request()->all();
        if ($d['has_child'] == 0) {
            $request->validate([
                'type' => ['required', 'in:url,group'],
                'target' => ['required', 'in:_self,_blank'],
            ]);
            switch ($d['type']) {
                case 'url':
                    $request->validate([
                        'url' => ['required'],
                    ]);
                    break;
                case 'group':
                    $request->validate([
                        'group_id' => ['required'],
                    ]);
                    break;
            }
        }

        $re = NaviNode::createNode($parent_id, $d['title'], $d['url'], $d['group_id'], $d['has_child'], $d['type'], $d['target']);
        if (!$re['success']) {
            return redirect()->back()->withErrors(['status' => $re['error_msg']]);
        }

        wToast('更新完成');
        return redirect(route('cms.navinode.index', ['level' => $level]));

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Collection $collection, $level = 0, $id)
    {
        $data = NaviNode::where('id', $id)->get()->first();

        if (!$data) {
            return abort(404);
        }

        return view('cms.frontend.navinode.edit', [
            'data' => $data,
            'method' => 'edit',
            'formAction' => Route('cms.navinode.edit', ['level' => $level, 'id' => $id]),
            'collections' => $collection->get()->toArray(),
            'breadcrumb_data' => ['level' => NaviNode::forBreadcrumb($level), 'title' => $data->title],
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $level = 0, $id)
    {
        //
        $request->validate([
            'title' => ['required', 'string'],
            'has_child' => 'required|in:0,1',
        ]);

        $d = request()->all();
        if ($d['has_child'] == 0) {
            $request->validate([
                'type' => ['required', 'in:url,group'],
                'target' => ['required', 'in:_self,_blank'],
            ]);
            switch ($d['type']) {
                case 'url':
                    $request->validate([
                        'url' => ['required'],
                    ]);
                    break;
                case 'group':
                    $request->validate([
                        'group_id' => ['required'],
                    ]);
                    break;
            }
        }
        $re = NaviNode::updateNode($id, $d['title'], $d['url'], $d['group_id'], $d['has_child'], $d['type'], $d['target']);
        if (!$re['success']) {
            return redirect()->back()->withErrors(['status' => $re['error_msg']]);
        }

        wToast('更新完成');
        return redirect(route('cms.navinode.index', ['level' => $level]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $level, $id)
    {
        //
        NaviNode::deleteNode($id);
        wToast('刪除完成');
        return redirect(route('cms.navinode.index', ['level' => $level]));

    }

    public function sort(Request $request){
        $request->validate([
            'id' => ['required', 'array'],
            'id.*' => 'numeric',
        ]);

        $id = $request->input('id');

        NaviNode::sort($id);

        return redirect()->back();
    }
}
