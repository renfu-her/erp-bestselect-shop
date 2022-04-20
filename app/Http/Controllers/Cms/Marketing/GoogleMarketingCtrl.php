<?php

namespace App\Http\Controllers\Cms\Marketing;

use App\Http\Controllers\Controller;
use App\Models\GoogleMarketing;
use Illuminate\Http\Request;

class GoogleMarketingCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('cms.marketing.google_marketing.list', [
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GoogleMarketing  $googleMarketing
     * @return \Illuminate\Http\Response
     */
    public function show(GoogleMarketing $googleMarketing)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GoogleMarketing  $googleMarketing
     * @return \Illuminate\Http\Response
     */
    public function edit(GoogleMarketing $googleMarketing)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GoogleMarketing  $googleMarketing
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GoogleMarketing $googleMarketing)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GoogleMarketing  $googleMarketing
     * @return \Illuminate\Http\Response
     */
    public function destroy(GoogleMarketing $googleMarketing)
    {
        //
    }

    /**
     * 新增Google廣告轉換追蹤事件
     */
    public function createGoogleAdsEvents()
    {
        return view('cms.marketing.google_marketing.ads_events_edit', [
        ]);
    }

    /**
     *
     * 建立Google廣告轉換追蹤事件
     */
    public function storeGoogleAdsEvents()
    {
        return view('cms.marketing.google_marketing.list', [
        ]);
    }

    /**
     * 編輯Google廣告轉換追蹤事件
     */
    public function editGoogleAdsEvents()
    {
        return view('cms.marketing.google_marketing.ads_events_edit', [
        ]);
    }
}
