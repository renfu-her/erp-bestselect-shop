@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">
        @if ($method === 'create')
            新增水果
        @else
            編輯
        @endif
    </h2>
    <form method="post" action="" enctype="multipart/form-data">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">水果名稱 <span class="text-danger">*</span></label>
                    <input class="form-control @error('title') is-invalid @enderror" type="text" 
                        name="title" value=""
                        placeholder="例：日本水蜜桃蘋果" required aria-label="水果名稱">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">副標題 <span class="text-danger">*</span></label>
                    <input class="form-control @error('sub_title') is-invalid @enderror" type="text" 
                        name="sub_title" value=""
                        placeholder="例：數量稀少、多汁爽脆的最佳口味" required aria-label="副標題">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">產地 <span class="text-danger">*</span></label>
                    <input class="form-control @error('place') is-invalid @enderror" type="text" 
                        name="place" value=""
                        placeholder="例：日本青森" required aria-label="產地">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">產季 <span class="text-danger">*</span></label>
                    <input class="form-control @error('season') is-invalid @enderror" type="text" 
                        name="season" value=""
                        placeholder="例：秋季 (10月)" required aria-label="產季">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">圖片網址 <span class="text-danger">*</span></label>
                    <input class="form-control @error('pic') is-invalid @enderror" type="url" 
                        name="pic" value=""
                        placeholder="例：https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_05_03.jpg" required aria-label="圖片網址">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">產品連結 <span class="text-danger">*</span></label>
                    <input class="form-control @error('link') is-invalid @enderror" type="url"
                        name="link" value=""
                        placeholder="例：https://www.bestselection.com.tw/product/P230508001?openExternalBrowser=1" required aria-label="產品連結">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">內文 <span class="text-danger">*</span></label>
                    <textarea name="text" class="form-control @error('text') is-invalid @enderror" 
                        placeholder="例：外表黃綠外帶有一抹腮紅，因此又被暱稱為『水蜜桃蘋果』。" aria-label="內文"></textarea>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">目前狀態 <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="" selected disabled>請選擇</option>
                        @foreach ($saleStatus as $key => $status)
                            <option value="{{ $status }}"
                                @class(['bg-success' => $key == 0, 'bg-danger' => $key == 13,
                                'bg-warning' => $key == 14, 'text-white' => $key == 0 || $key == 13])>
                                {{ $status }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ Route('cms.act-fruits.index') }}" class="btn btn-outline-primary px-4"
                role="button">返回列表</a>
        </div>
    </form>

@endsection
@once
    @push('sub-scripts')
    @endpush
@endonce
