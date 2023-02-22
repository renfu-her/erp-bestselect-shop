<?php

namespace App\Http\Controllers\Cms\Settings;

use App\Enums\Globals\AppEnvClass;
use App\Http\Controllers\Controller;
use App\Models\ImgStorage;
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
        $user_name = Arr::get($query, 'user_name');
        $sDate = Arr::get($query, 'sDate');
        $eDate = Arr::get($query, 'eDate');

        $dataList = ImgStorage::dataList($user_name, $sDate, $eDate);

        return view('cms.settings.img_storage.index', [
            'user' => $request->user()->name,
            'dataList' => $dataList->paginate(10)->appends($query),
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
            'file' => 'mimes:jpg,jpeg,png,bmp|required',
        ]);
        $img = self::imgResize($request->file('file')->path());

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

    private static function imgResize($path)
    {
        try {
            $asdf = Image::make($path)
                ->resize(1000, 1000, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->encode('webp', 90);
        } catch (\Exception $e) {
            dd($e);
            return false;
        }
        return $asdf;
    }

    private static function imgFilename($product_id, $fileHashName)
    {
        return 'product_imgs/content/' . $product_id . '/' . explode('.', $fileHashName)[0] . ".webp";

    }
}
