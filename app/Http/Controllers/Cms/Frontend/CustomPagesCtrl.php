<?php

namespace App\Http\Controllers\Cms\Frontend;

use App\Enums\FrontEnd\CustomPageType;
use App\Enums\SaleChannel\Channel;
use App\Http\Controllers\Controller;
use App\Models\CustomPages;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomPagesCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $request->query();
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;
        $dataList = DB::table('csp_custom_pages')
                    ->leftJoin('usr_users', 'csp_custom_pages.usr_users_id_fk', '=', 'usr_users.id')
                    ->leftJoin('csp_activity_html as html','csp_custom_pages.csp_html_type_fk','html.id')
                    ->select([
                        'csp_custom_pages.id',
                        'page_name',
                        'url',
                        'type',
                        'usr_users.name AS user_name',
                        'csp_custom_pages.updated_at',
                        'html.body'
                    ])
                    ->get();


        return view('cms.frontend.custom_pages.list', [
            'dataList' => $dataList,
            'data_per_page' => $data_per_page
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //暫時只用電商通路
        $salesChannels = DB::table('prd_sale_channels')
            ->where('id', '=', Channel::Ecommerce)
            ->select(['id', 'title'])
            ->get();

        $userName = User::where('id', $request->user()->id)
            ->select('name as user_name')
            ->get()
            ->first();

        $customPagesType = CustomPageType::asSelectArray();
        return view('cms.frontend.custom_pages.edit', [
            'method' => 'create',
            'userName' => $userName,
            'customPagesType' => $customPagesType,
            'salesChannels' => $salesChannels,
            'formAction' => Route('cms.custom-pages.create'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'page_name'  => [
                'required',
                'string',
                'unique:App\Models\CustomPages,page_name'
            ],
            'url'  => [
                'nullable',
                'string',
                'unique:App\Models\CustomPages,url'
            ],
            'title'  => [
                'nullable',
                'string',
                'unique:App\Models\CustomPages,title'
            ],
            'desc' => 'nullable|string',
            'sale_channel'  => [
                'required',
                'string',
                'exists:App\Models\SaleChannel,id'
            ],
            'type'        => ['required', 'int', 'min:1', 'max:2'],
            'content' => 'nullable|string',
            'head' => 'nullable|string',
            'body' => 'nullable|string',
            'script' => 'nullable|string',
        ]);

        $usr_users_id_fk = $request->user()->id;
        $input = $request->all();
        $input['usr_users_id_fk'] = $usr_users_id_fk;

        CustomPages::storeCustomPages($input);

        wToast('儲存成功');
        return redirect(Route('cms.custom-pages.index'));
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
    public function edit(Request $request, int $id)
    {
        //暫時只用電商通路
        $salesChannels = DB::table('prd_sale_channels')
            ->where('id', '=', Channel::Ecommerce)
            ->select(['id', 'title'])
            ->get();

        $customPagesType = CustomPageType::asSelectArray();
        $dataList = CustomPages::getDataListById($id);
        $userName = User::where('id', $request->user()->id)
            ->select('name as user_name')
            ->get()
            ->first();

        return view('cms.frontend.custom_pages.edit', [
            'method' => 'edit',
            'dataList' => $dataList,
            'userName' => $userName,
            'customPagesType' => $customPagesType,
            'salesChannels' => $salesChannels,
            'formAction' => Route('cms.custom-pages.edit', $id),
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
        $request->validate([
            'page_name'  => [
                'required',
                'string',
                Rule::unique('csp_custom_pages', 'page_name')->ignore($id),
            ],
            'url'  => [
                'nullable',
                'string',
                Rule::unique('csp_custom_pages', 'url')->ignore($id),
            ],
            'title'  => [
                'nullable',
                'string',
                Rule::unique('csp_custom_pages', 'title')->ignore($id),
            ],
            'desc' => 'nullable|string',
            'sale_channel'  => [
                'required',
                'string',
                'exists:App\Models\SaleChannel,id'
            ],
            'type'        => ['required', 'int', 'min:1', 'max:2'],
            'content' => 'nullable|string',
            'head' => 'nullable|string',
            'body' => 'nullable|string',
            'script' => 'nullable|string',
        ]);

        $usr_users_id_fk = $request->user()->id;
        $input = $request->all();
        $input['usr_users_id_fk'] = $usr_users_id_fk;
        $input['id'] = $id;

        CustomPages::updateCustomPages($input);

        wToast('更新成功');
        return redirect(Route('cms.custom-pages.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customPage = CustomPages::find($id);

        if ($customPage->type == CustomPageType::General) {
            DB::table('csp_general_html')
                ->where('id', '=', $customPage->csp_html_type_fk)
                ->delete();
        } elseif ($customPage->type == CustomPageType::Activity) {
            DB::table('csp_activity_html')
                ->where('id', '=', $customPage->csp_html_type_fk)
                ->delete();
        }

        $customPage->delete();

        wToast('刪除成功');
        return redirect(Route('cms.custom-pages.index'));
    }
}
