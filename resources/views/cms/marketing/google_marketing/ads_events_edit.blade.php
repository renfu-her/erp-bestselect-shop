@extends('layouts.main')
@section('sub-content')
    <form method="post" action={{ Route('cms.google_marketing.store_ads_events', null, true) }}>
        @csrf
        <h2 class="mb-4">新增Google Ads 轉換追蹤</h2>
        <div class="card shadow p-4 mb-4">
            <div class="col-12">
                <label for="ads_conversion">轉換追蹤事件<span class="text-danger">*</span></label>
                <select id="ads_conversion" class="form-select @error('ads_conversion') is-invalid @enderror"
                        name="ads_conversion">
                    <option>請選擇</option>
                    @foreach (\App\Enums\Marketing\GoogleAdsConversion::asSelectArray() as $key => $data)
                        <option value="{{ $key }}">{{ $data }}</option>
                    @endforeach
                </select>
                <x-b-form-group name="ads_id" title="追蹤編號" required="true">
                    <input class="form-control @error('ads_id') is-invalid @enderror"
                           name="ads_id"
                           value="{{ old('ads_id', $data['ads_id'] ?? '') }}"
                           placeholder="例：436409888"/>
                </x-b-form-group>
                <x-b-form-group name="ads_tag" title="追蹤標籤" required="true">
                    <input class="form-control @error('ads_tag') is-invalid @enderror"
                           name="ads_tag"
                           value="{{ old('ads_tag', $data['ads_tag'] ?? '') }}"
                           placeholder="例：yXlDCIbcicUCEKCsjNAB"/>
                </x-b-form-group>
            </div>
            <div class="col-12">
                <div class="justify-content-start mt-3">
                    <button type="submit" class="btn btn-primary px-4">儲存</button>
                    <a href="{{ Route('cms.google_marketing.index', [], true) }}">
                        <button type="button" class="btn btn-outline-primary px-4" id="cancelBtn">取消</button>
                    </a>
                </div>
            </div>
            <div>
            </div>
        </div>
    </form>

@endsection
