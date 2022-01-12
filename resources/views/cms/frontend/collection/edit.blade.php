@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.collection.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>
    <h2 class="mb-4">
        @if ($method === 'create') 新增 @else 編輯 @endif 商品群組
    </h2>
    <form class="card-body" method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <x-b-form-group name="name" title="商品群組名稱" required="true">
                <input type="text"
                       class="form-control @error('name') is-invalid @enderror"
                       id="name"
                       name="name"
                       value="{{ old('name', $name ?? '')}}"
                       aria-label="商品群組名稱"/>
                @error('name')
                <div class="alert-danger"> {{ $message }} </div>
                @enderror
            </x-b-form-group>
            <p class="mark m-0"><i class="bi bi-exclamation-diamond-fill mx-2 text-warning"></i>
                系統會自動將「商品群組名稱」代入網頁連結、標題、描述中
                ，如需調整搜尋引擎SEO成效，可自行修改
            </p>
            <x-b-form-group name="url" title="網頁連結" required="false">
                <input type="text"
                       class="form-control @error('url') is-invalid @enderror"
                       id="url"
                       name="url"
                       value="{{ old('url', $url ?? '')}}"
                       aria-label="網頁連結"/>
                @error('url')
                <div class="alert-danger"> {{ $message }} </div>
                @enderror
            </x-b-form-group>
            <x-b-form-group name="meta_title" title="網頁標題" required="false">
                <input type="text"
                       class="form-control @error('meta_title') is-invalid @enderror"
                       id="meta_title"
                       name="meta_title"
                       value="{{ old('meta_title', $meta_title ?? '')}}"
                       aria-label="網頁標題"/>
                @error('meta_title')
                <div class="alert-danger"> {{ $message }} </div>
                @enderror
            </x-b-form-group>
            <x-b-form-group name="meta_description" title="網頁描述" required="false">
                <input type="text"
                       class="form-control @error('meta_description') is-invalid @enderror"
                       id="meta_description"
                       name="meta_description"
                       value="{{ old('meta_description', $meta_description ?? '')}}"
                       aria-label="網頁描述"/>
                @error('meta_description')
                <div class="alert-danger"> {{ $message }} </div>
                @enderror
            </x-b-form-group>
        </div>
        <div class="card shadow p-4 mb-4">
            <h6>新增商品</h6>
{{--            TODO add Search Product API and jQuery post--}}
            <div class="d-grid mt-3">
                <button type="button"
                        class="btn btn-outline-primary border-dashed add_ship_rule"
                        style="font-weight: 500;">
                    <i class="bi bi-plus-circle bold"></i> 加入商品
                </button>
            </div>
        </div>
        <div class="d-flex justify-content-end pt-2">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
    @endpush
@endonce
