@extends('layouts.main')
@section('sub-content')
<div>
    <h2 class="mb-3">{{ $data->title }}</h2>
    <x-b-prd-navi id="{{  $data->id }}"></x-b-prd-navi>
</div>
<form action="">
    <div class="card shadow p-4 mb-4">
        <h6>規格說明（官網）</h6>
        <div class="sortabled mb-3 -appendClone">
            <div class="mb-2 row -oneitem ">
                <div class="col d-flex flex-column flex-sm-row pe-0 -cloneElem">
                    <div class="col col-sm-5 col-md-4 col-lg-3 px-0 pb-2 pb-sm-0">
                        <input type="text" class="form-control" maxlength="10" placeholder="請輸入標題。例：材質" aria-label="規格說明標題">
                    </div>
                    <div class="col px-0 px-sm-2 pb-2 pb-sm-0">
                        <input type="text" class="form-control" placeholder="請輸入內文。例：棉 / 聚脂纖維" aria-label="規格說明內文">
                    </div>
                </div>
                <div class="col-auto d-flex flex-column flex-sm-row ps-0">
                    <button type="button" class="icon -move icon-btn fs-5 text-primary rounded-circle border-0 p-0">
                        <i class="bi bi-arrows-move"></i>
                    </button>
                    <button type="button" class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="w-100 my-2 p-0 dropdown-divider d-block d-sm-none"></div>
            </div>
        </div>
    </div>
    <div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ Route('cms.product.index') }}" class="btn btn-outline-primary px-4" role="button">返回列表</a>
        </div>
    </div>
</form>
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
