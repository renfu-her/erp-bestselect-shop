<?php

namespace App\Http\Controllers\Cms\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\OnePage;
use App\Models\SaleChannel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class OnePageCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $query = $request->query();

        $title = Arr::get($query, 'title', '');

        $dataList = OnePage::dataList($title)->paginate(100)->appends($query); // $collection->getDataList($query);
        //    $data_per_page = Arr::get($query, 'data_per_page', 10);

        $data_per_page = 10; // is_numeric($data_per_page) ? $data_per_page : 10;
        $mcode = $request->user()->getUserCustomer($request->user()->id)->sn;

        return view('cms.onepage.list', [
            'dataList' => $dataList,
            'data_per_page' => $data_per_page,
            'mcode' => $mcode,
            //  'topList' => Collection::where('is_liquor', 0)->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('cms.onepage.edit', [
            'method' => 'create',
            'saleChannel' => SaleChannel::get(),
            'collection' => Collection::get(),
            'formAction' => Route('cms.onepage.create'),
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
        //
        $request->validate([
            'title' => 'required',
            'collection_id' => 'required',
            'sale_channel_id' => 'required',
            'online_pay' => 'required',
            'view_mode' => 'required',
        ]);

        $d = $request->all();

        $id = OnePage::create([
            'title' => $d['title'],
            'collection_id' => $d['collection_id'],
            'sale_channel_id' => $d['sale_channel_id'],
            'online_pay' => $d['online_pay'],
            'view_mode' => $d['view_mode'],
            'country' => $d['country'],
        ])->id;

        $img = self::imgResize($request->file('img')->path());
       
        $filename = self::imgFilename($id, $request->file('img')->hashName());
        if (Storage::disk('local')->put($filename, $img)) {
            OnePage::where('id',$id)->update(['img'=>$filename]);
        }


        wToast('新增完成');

        return redirect(route('cms.onepage.index'));

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
    public function edit($id)
    {
        //
        $data = OnePage::where('id', $id)->get()->first();

        if (!$data) {
            return abort(404);
        }

        return view('cms.onepage.edit', [
            'method' => 'edit',
            'saleChannel' => SaleChannel::get(),
            'collection' => Collection::get(),
            'formAction' => Route('cms.onepage.edit', ['id' => $id]),
            'data' => $data,
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
        $request->validate([
            'title' => 'required',
            'collection_id' => 'required',
            'sale_channel_id' => 'required',
            'online_pay' => 'required',
            'view_mode' => 'required',
        ]);

        $d = $request->all();
        $_img = null;
        $img = self::imgResize($request->file('img')->path());
       
        $filename = self::imgFilename($id, $request->file('img')->hashName());
        if (Storage::disk('local')->put($filename, $img)) {
          //  dd('aa');
            $_img = $filename;
        }
      //  dd($_img);

        $updateData = [
            'title' => $d['title'],
            'collection_id' => $d['collection_id'],
            'sale_channel_id' => $d['sale_channel_id'],
            'online_pay' => $d['online_pay'],
            'view_mode' => $d['view_mode'],
            'country' => $d['country'],
        ];

        if ($_img) {
            $updateData['img'] = $_img;
        }

        OnePage::where('id', $id)->update($updateData);

        wToast('修改完成');

        return redirect(route('cms.onepage.index'));

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
        OnePage::where('id', $id)->delete();

        wToast('刪除完成');

        return redirect(route('cms.onepage.index'));

    }

    public function active(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|int|min:0',
        ]);

        $re = $request->all();

        OnePage::changeActiveStatus($re['id']);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'errors' => $validator->errors()], 400);
        }

        return response()->json(['status' => 'success']);
    }

    public function activeApp(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|int|min:0',
        ]);

        $re = $request->all();

        OnePage::changeAppStatus($re['id']);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'errors' => $validator->errors()], 400);
        }

        return response()->json(['status' => 'success']);
    }

    public static function imgResize($path)
    {
        //             ->resize(1360, 453)
        return Image::make($path)->encode('webp', 90);
    }

    public static function imgFilename($banner_id, $fileHashName)
    {
        return 'idx_banner/' . $banner_id . '/' . explode('.', $fileHashName)[0] . ".webp";

    }

}
