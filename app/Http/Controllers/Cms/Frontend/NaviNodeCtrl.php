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
    /*
    public function index(Request $request, $level = 0)
    {

    // dd(NaviNode::tree(0));
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
     */
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        // dd(NaviNode::tree());
        return view('cms.frontend.navinode.new-list', [
            'dataList' => NaviNode::tree(),
            'breadcrumb_data' => [],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create(Request $request, Collection $collection)
    {
        return view('cms.frontend.navinode.new-edit', [
            'method' => 'create',
            'formAction' => Route('cms.navinode.create'),
            'collections' => $collection->get()->toArray(),
            'breadcrumb_data' => NaviNode::forBreadcrumb(0),
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

        $re = NaviNode::createNode($parent_id, $d['title'], $d['url'], $d['event_id'], $d['has_child'], $d['event'], $d['target']);
        if (!$re['success']) {
            return redirect()->back()->withErrors(['status' => $re['error_msg']]);
        }

        wToast('更新完成');
        return redirect(route('cms.navinode.index'));

    }

    public function vali($request, &$d = [], $level)
    {

        // $currentLevel = count(explode('-', $level));
        $valuRule = [
            'title' => ['required', 'string'],
        ];

        if ($level != 3) {
            $valuRule['has_child'] = 'required|in:0,1';
        };

        $request->validate($valuRule);

        $data = request()->all();

        $d['url'] = null;
        $d['event_id'] = null;
        $d['event'] = null;
        $d['url'] = null;
        $d['target'] = null;
        $d['has_child'] = isset($data['has_child']) ? $data['has_child'] : 0;
        $d['title'] = $data['title'];

        if ($level >= 3) {
            $d['has_child'] = 0;
        }

        if ($d['has_child'] == 0) {

            $request->validate([
                'event' => ['required', 'in:url,group'],
                'target' => ['required', 'in:_self,_blank'],
            ]);
            $d['event'] = $data['event'];
            $d['target'] = $data['target'];

            switch ($d['event']) {
                case 'url':
                    $request->validate([
                        'url' => ['required'],
                    ]);
                    $d['url'] = $data['url'];
                    break;
                case 'group':
                    $request->validate([
                        'event_id' => ['required'],
                    ]);

                    $d['event_id'] = $data['event_id'];

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
    public function edit(Request $request, Collection $collection, $id)
    {
        $data = NaviNode::where('id', $id)->get()->first();
        
        if (!$data) {
            return abort(404);
        }

        // $currentLevel = count(explode('-', $level));
        return view('cms.frontend.navinode.new-edit', [
            'data' => $data,
            'method' => 'edit',
            'level' => $data->level,
            'currentLevel' => $data->level,
            'formAction' => Route('cms.navinode.edit', ['id' => $id]),
            'collections' => $collection->get()->toArray(),
            'breadcrumb_data' => [],
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $node = NaviNode::where('id', $id)->get()->first();
        $this->vali($request, $d, $node->level);

        $re = NaviNode::updateNode($id, $d['title'], $d['url'], $d['event_id'], $d['has_child'], $d['event'], $d['target']);
        if (!$re['success']) {
            return redirect()->back()->withErrors(['status' => $re['error_msg']]);
        }

        wToast('更新完成');
        return redirect(route('cms.navinode.index'));
    }

    public function updateLevel(Request $request)
    {
        $d = $request->all();

        if ($d['del_id']) {
            $del_id = explode(',', $d['del_id']);
            if (count($del_id) > 0) {
                NaviNode::whereIn('id', $del_id)->delete();
            }
        }

        $data = json_decode($d['data']);

        NaviNode::updateMultiLevel($data);
        NaviNode::cacheProcess();
        //  dd(NaviNode::tree());
        wToast('變更完成');
        return redirect()->back();

    }

   

   
}
