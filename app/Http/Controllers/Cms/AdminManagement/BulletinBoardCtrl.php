<?php

namespace App\Http\Controllers\Cms\AdminManagement;

use App\Enums\AdminManagement\Weight;
use App\Http\Controllers\Controller;
use App\Models\BulletinBoard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BulletinBoardCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string',
            'content' => 'nullable|string',
            'user' => 'nullable|array',
            'user.*' => ['nullable',
                         'int',
                         'exists:App\Models\User,id'],
        ]);

        $query = $request->query();
        $cond['title'] = Arr::get($query, 'title', '');
        $cond['user'] = Arr::get($query, 'user', []);
        $cond['content'] = Arr::get($query, 'content', '');
        $page = getPageCount(Arr::get($query, 'data_per_page'));

        $q = BulletinBoard::leftJoin('usr_users', 'idx_news.usr_users_id_fk', '=', 'usr_users.id');
        if (!empty($cond['title'])) {
            $q->where('idx_news.title', 'like', '%'.$cond['title'].'%');
        }
        if (!empty($cond['user'])) {
            $q->whereIn('usr_users.id', $cond['user']);
        }
        if (!empty($cond['content'])) {
            $q->where('idx_news.content', 'like', '%'.$cond['content'].'%');
        }
        $dataList = $q->select([
                'idx_news.id',
                'idx_news.title',
                'idx_news.content',
                'idx_news.weight',
                'idx_news.expire_time',
                'usr_users.name as user_name',
            ])
            ->paginate($page)
            ->appends($query);

        return view('cms.admin_management.bulletin_board.list', [
            'dataList' => $dataList,
            'users' => User::all(['id', 'name']),
            'cond' => $cond,
            'data_per_page' => $page,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $weights = [];
        foreach (Weight::getValues() as $value) {
            $weights[$value] = Weight::getDescription($value);
        }

        return view('cms.admin_management.bulletin_board.edit', [
            'method' => 'create',
            'weights' => $weights,
            'formAction' => Route('cms.bulletin_board.create'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'weight'  => [
                'required',
                'int',
                Rule::in(Weight::getValues()),
            ],
            'expire_time' => [
                'required',
                'date_format:Y-m-d',
            ],
        ]);
        $data = $request->all();

        BulletinBoard::create([
            'title' => $data['title'],
            'content' => $data['content'],
            'weight' => $data['weight'],
            'expire_time' => $data['expire_time'],
            'usr_users_id_fk' => $request->user()->id,
        ]);

        wToast('新增完成');
        return redirect(Route('cms.bulletin_board.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function show($id)
    {
        $data =DB::table('idx_news')
                ->where('id', $id)
                ->get()
                ->first();
        return view('cms.admin_management.bulletin_board.show', [
            'data' => $data,
            'weight_title' => Weight::getDescription($data->weight),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $weights = [];
        foreach (Weight::getValues() as $value) {
            $weights[$value] = Weight::getDescription($value);
        }

        $data = BulletinBoard::where('idx_news.id', $id)
            ->leftJoin('usr_users', 'usr_users.id', '=', 'idx_news.usr_users_id_fk')
            ->select([
                'idx_news.id',
                'idx_news.title',
                'idx_news.content',
                'idx_news.weight',
                'idx_news.expire_time',
                'usr_users.name as user_name',
            ])
            ->get()
            ->first();

        return view('cms.admin_management.bulletin_board.edit', [
            'method' => 'edit',
            'data' => $data,
            'formAction' => Route('cms.bulletin_board.edit', $id),
            'weights' => $weights,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'weight'  => [
                'required',
                'int',
                Rule::in(Weight::getValues()),
            ],
            'expire_time' => [
                'required',
                'date_format:Y-m-d',
            ],
        ]);
        $data = $request->all();
        BulletinBoard::where('id', $id)
            ->update([
                'title' => $data['title'],
                'content' => $data['content'],
                'weight' => $data['weight'],
                'expire_time' => $data['expire_time'],
            ]);

        wToast('檔案更新完成');
        return redirect(Route('cms.bulletin_board.index'));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        BulletinBoard::where('id', $id)->delete();
        wToast('資料刪除完成');
        return redirect(Route('cms.bulletin_board.index'));
    }
}
