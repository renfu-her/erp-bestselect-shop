@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">Google數位行銷</h2>
    <div class="card shadow p-4 mb-4">
        <h4>追蹤編號設定</h4>
        <x-b-form-group name="ga_3" title="舊版通用Google Analytics（追蹤編號含UA-xxxxxxxxx）" required="false">
            <input class="form-control @error('ga_3') is-invalid @enderror"
                   name="ga_3"
                   value="{{ old('ga_3', $data->ga_3 ?? '') }}"
                   placeholder="例：UA-123456-2"/>
        </x-b-form-group>
        <x-b-form-group name="ga_4" title="新版Google Analytics 4（追蹤編號含G-xxxxxxxx）" required="false">
            <input class="form-control @error('ga_4') is-invalid @enderror"
                   name="ga_4"
                   value="{{ old('ga_4', $data->ga_4 ?? '') }}"
                   placeholder="例：G-ABCDEF"/>
        </x-b-form-group>
        <x-b-form-group name="gtm" title="Google Tag Manager（追蹤編號含GTM-xxxxxxxx）" required="false">
            <input class="form-control @error('gtm') is-invalid @enderror"
                   name="gtm"
                   value="{{ old('gtm', $data->gtm ?? '') }}"
                   placeholder="例：GTM-ABCDEFG"/>
        </x-b-form-group>
        <x-b-form-group name="remarketing" title="Google Ads 再行銷（編號僅含數字conversion_id=xxxxxxxx）" required="false">
            <input class="form-control @error('remarketing') is-invalid @enderror"
                   name="remarketing"
                   value="{{ old('remarketing', $data->remarketing ?? '') }}"
                   placeholder="例：12345678"/>
        </x-b-form-group>
        <div class="d-flex justify-content-start mt-3">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
        </div>
    </div>

    <div class="card shadow p-4 mb-4">
        <h4>Google Ads 轉換追蹤</h4>
        <div class="col">
                <a href="{{ Route('cms.google_marketing.create_ads_events', null, true) }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i>新增轉換追蹤事件
                </a>
        </div>
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                <tr>
                    <th scope="col" style="width:10%">#</th>
                    <th scope="col">追蹤事件</th>
                    <th scope="col">追蹤編號</th>
                    <th scope="col">追蹤標籤</th>
                    <th scope="col">編輯</th>
                    <th scope="col">刪除</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $adsId = '436409888';
                    $adsTag = 'yXlDCIbcicUCEKCsjNAB';
                    $dataList = [];
                    for ($index = 1; $index <= 5; $index++) {
                        $dataList[] = [
                            'id' => $index,
                            'event' => \App\Enums\Marketing\GoogleAdsConversion::getDescription($index),
                            'ads_id' => $adsId,
                            'ads_tag' => $adsTag,
                        ];
                    }
                @endphp
                @foreach ($dataList ?? [] as $key => $data)
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $data['event'] }}</td>
                        <td>{{ $data['ads_id'] }}</td>
                        <td>{{ $data['ads_tag'] }}</td>
                        <td>
                            <a href="{{ Route('cms.google_marketing.edit_ads_events', ['id' => $data['id']], true) }}"
                               data-bs-toggle="tooltip" title="編輯"
                               class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </td>
                        <td>
                            <a href="javascript:void(0)"
                               data-href="{{ Route('cms.google_marketing.delete', ['id' => $data['id']], true) }}"
                               data-bs-toggle="modal" data-bs-target="#confirm-delete"
                               class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
