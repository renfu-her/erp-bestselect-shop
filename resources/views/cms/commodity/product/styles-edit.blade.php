@extends('layouts.main')
@section('sub-content')
<div>
    <h2 class="mb-3">{{ $data->title }}</h2>
    <x-b-prd-navi id="{{  $data->id }}"></x-b-prd-navi>
</div>

<div class="card shadow p-4 mb-4">
    <h6>編輯規格</h6>
    <label>規格最多只能選擇三種</label>
    <div></div>
</div>

<div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary px-4">儲存</button>
        <a href="{{ Route('cms.product.edit-style', ['id' => $data->id]) }}" class="btn btn-outline-primary px-4" role="button">返回列表</a>
    </div>
</div>


@endsection
@once
    @push('sub-styles')
    <style>
    </style>
    @endpush
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endOnce
