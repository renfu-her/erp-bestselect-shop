<?php

namespace App\Http\Controllers\Cms\Frontend;

use App\Enums\Globals\FrontendApiUrl;
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

        dd(NaviNode::tree(0));
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
        //  dd(NaviNode::nodeList($parent_id)->get()->toArray());
        return view('cms.frontend.navinode.list', [
            'dataList' => NaviNode::nodeList($parent_id)->get()->toArray(),
            'level' => $level,
            'prev' => $prev,
            'breadcrumb_data' => NaviNode::forBreadcrumb($level),
        ],
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function design()
    {   
       
        dd(NaviNode::tree());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create(Request $request, Collection $collection, $level = 0)
    {
        $currentLevel = count(explode('-', $level));

        return view('cms.frontend.navinode.edit', [
            'method' => 'create',
            'level' => $level,
            'currentLevel' => $currentLevel,
            'formAction' => Route('cms.navinode.create', ['level' => $level]),
            'collections' => $collection->get()->toArray(),
            'breadcrumb_data' => NaviNode::forBreadcrumb($level),
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
        $this->vali($request, $d, $level);

        $re = NaviNode::createNode($parent_id, $d['title'], $d['url'], $d['collection_id'], $d['has_child'], $d['type'], $d['target']);
        if (!$re['success']) {
            return redirect()->back()->withErrors(['status' => $re['error_msg']]);
        }

        wToast('更新完成');
        return redirect(route('cms.navinode.index', ['level' => $level]));

    }

    public function vali($request, &$d = [], $level)
    {
        $currentLevel = count(explode('-', $level));
        $valuRule = [
            'title' => ['required', 'string'],
        ];
        if ($currentLevel != 3) {
            $valuRule['has_child'] = 'required|in:0,1';
        };

        $request->validate($valuRule);

        $data = request()->all();

        $d['url'] = null;
        $d['collection_id'] = null;
        $d['type'] = null;
        $d['url'] = null;
        $d['target'] = null;
        $d['has_child'] = isset($data['has_child']) ? $data['has_child'] : 0;
        $d['title'] = $data['title'];

        if ($currentLevel >= 3) {
            $d['has_child'] = 0;
        }

        if ($d['has_child'] == 0) {

            $request->validate([
                'type' => ['required', 'in:url,group'],
                'target' => ['required', 'in:_self,_blank'],
            ]);
            $d['type'] = $data['type'];
            $d['target'] = $data['target'];

            switch ($d['type']) {
                case 'url':
                    $request->validate([
                        'url' => ['required'],
                    ]);
                    $d['url'] = $data['url'];
                    break;
                case 'group':
                    $request->validate([
                        'collection_id' => ['required'],
                    ]);

                    $d['collection_id'] = $data['collection_id'];

                    break;
            }
        }

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

        $currentLevel = count(explode('-', $level));

        return view('cms.frontend.navinode.edit', [
            'data' => $data,
            'method' => 'edit',
            'level' => $level,
            'currentLevel' => $currentLevel,
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

        $this->vali($request, $d, $level);

        $re = NaviNode::updateNode($id, $d['title'], $d['url'], $d['collection_id'], $d['has_child'], $d['type'], $d['target']);
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

    public function sort(Request $request)
    {
        $request->validate([
            'id' => ['required', 'array'],
            'id.*' => 'numeric',
        ]);

        $id = $request->input('id');

        NaviNode::sort($id);
        wToast('排序完成');
        return redirect()->back();
    }
}
