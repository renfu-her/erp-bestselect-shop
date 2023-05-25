@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">
        @if ($method === 'create')
            新增
        @else
            編輯
        @endif 水果
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
                        placeholder="輸入水果名稱" required aria-label="水果名稱">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">副標題 <span class="text-danger">*</span></label>
                    <input class="form-control @error('sub_title') is-invalid @enderror" type="text" 
                        name="sub_title" value=""
                        placeholder="輸入副標題" required aria-label="副標題">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">產地 <span class="text-danger">*</span></label>
                    <input class="form-control @error('place') is-invalid @enderror" type="text" 
                        name="place" value=""
                        placeholder="輸入產地" required aria-label="產地">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">產季 <span class="text-danger">*</span></label>
                    <input class="form-control @error('season') is-invalid @enderror" type="text" 
                        name="season" value=""
                        placeholder="輸入產季" required aria-label="產季">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">圖片網址 <span class="text-danger">*</span></label>
                    <input class="form-control @error('pic') is-invalid @enderror" type="text" 
                        name="pic" value=""
                        placeholder="輸入圖片網址" required aria-label="圖片網址">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">內文 <span class="text-danger">*</span></label>
                    <textarea name="text" class="form-control @error('text') is-invalid @enderror" 
                        aria-label="內文"></textarea>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">目前狀態 <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="" selected disabled>請選擇</option>
                        <option value="" class="bg-success text-white">販售中</option>
                        <option value="" class="bg-danger text-white">已售罄</option>
                        <option value="" class="bg-warning">今年產季已過</option>
                        <option value="">1月開放預購</option>
                        <option value="">2月開放預購</option>
                        <option value="">3月開放預購</option>
                        <option value="">4月開放預購</option>
                        <option value="">5月開放預購</option>
                        <option value="">6月開放預購</option>
                        <option value="">7月開放預購</option>
                        <option value="">8月開放預購</option>
                        <option value="">9月開放預購</option>
                        <option value="">10月開放預購</option>
                        <option value="">11月開放預購</option>
                        <option value="">12月開放預購</option>
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
    @push('sub-styles')
        <style>
            /* 拖曳預覽框 */
            .-appendClone.--selectedP tr.placeholder-highlight {
                width: 100%;
                height: 60px;
                margin-bottom: .5rem;
                display: table-row;
            }
            tr.placeholder-highlight > td {
                border: none;
            }
        </style>
    @endpush
    @push('sub-scripts')
    @endpush
@endonce
