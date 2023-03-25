<?php

namespace App\Http\Controllers\Cms\Settings;

use App\Enums\Globals\AppEnvClass;
use App\Http\Controllers\Controller;
use App\Models\ImgStorage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImgStroageCtrl extends Controller
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
        $user_name = Arr::get($query, 'user_name', $request->user()->name);
        $sDate = Arr::get($query, 'sDate');
        $eDate = Arr::get($query, 'eDate');

        $userRoleTitle = User::getRoleTitleByUserId($request->user()->id);
        $is_SA = false;
        foreach ($userRoleTitle as $role) {
            if ($role->title === '超級管理員') {
                $is_SA = true;
            }
        }

        $dataList = ImgStorage::dataList($user_name, $sDate, $eDate);

        return view('cms.settings.img_storage.index', [
            'user' => $user_name,
            'is_SA' => $is_SA,
            'dataList' => $dataList->paginate(12)->appends($query),
        ]);
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

        $request->validate([
            'file' => 'max:5000000|mimes:jpg,jpeg,png,bmp|required',
        ]);
        $compress = $request->input('no_compress') ? false : true;
        $w = $request->input('sa_w');
        $h = $request->input('sa_h');
        $img = self::imgResize($request->file('file')->path(), $compress, $w, $h);

        $filename = self::imgFilename($request->user()->id, $request->file('file')->hashName());

        if (App::environment(AppEnvClass::Release)) {
            if (Storage::disk('ftp')->put($filename, $img)) {
                $imgData = $filename;
            }
        } else {
            if (Storage::disk('local')->put($filename, $img)) {
                $imgData = $filename;
            }
        }
        if ($imgData) {
            ImgStorage::create([
                'user_id' => $request->user()->id,
                'url' => $imgData,
            ]);

        }

        return redirect(route('cms.img-storage.index'));
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

    private static function imgResize($path, $compress, $w, $h)
    {
        try {
            $asdf = Image::make($path);

            if ($compress) {    // 要壓縮
                $asdf = $asdf->resize(1000, 1000, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->encode('webp', 70);
            } else {    // 不壓縮
                if ($w !== null || $h !== null) {
                    $asdf = $asdf->resize($w, $h, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }
                $asdf = $asdf->encode('webp');
            }
        } catch (\Exception $e) {
            dd($e);
            return false;
        }
        return $asdf;
    }

    private static function imgFilename($product_id, $fileHashName)
    {
        return 'product_intro/imgs/' . $product_id . '/' . explode('.', $fileHashName)[0] . ".webp";

    }
}
