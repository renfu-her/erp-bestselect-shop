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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
