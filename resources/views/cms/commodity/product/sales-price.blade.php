@extends('layouts.main')
@section('sub-content')
<div>
    <h2 class="mb-3">{{ $product->title }}</h2>
    <x-b-prd-navi :product="$product"></x-b-prd-navi>
</div>
<div class="card shadow p-4 mb-4">
    <div class="d-flex align-items-center mb-4">
        <h6 class="flex-grow-1 mb-0">商品資訊</h6>
        <a href="{{ Route('cms.product.edit-stock', ['id' => $style['product_id'], 'sid' => $style['id']]) }}"
            class="btn btn-outline-primary px-4 -in-header">
            <i class="bi bi-arrow-left-right"></i> 庫存管理
        </a>
    </div>
    <div class="row">
        <div class="col-12 mb-3">
            <label class="form-label">款式</label>
            <input class="form-control" type="text" value="S、R" readonly aria-label="款式">
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">負責人</label>
            <input class="form-control" type="text" value="施欽元" readonly aria-label="負責人">
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">廠商名稱</label>
            <input class="form-control" type="text" value="BANNIES" readonly aria-label="廠商名稱">
        </div>
    </div>
</div>
<form action="" method="POST">
    <div class="card shadow p-4 mb-4">
        <h6>售價資訊</h6>
        <div class="table-responsive tableOverBox">
            <table class="table tableList table-hover mb-1">
                <thead>
                    <tr>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @foreach ($styles as $styleKey => $style) --}}
                        <tr>
                            <td></td>
                        </tr>
                    {{-- @endforeach --}}
                </tbody>
            </table>
        </div>
    </div>
    <div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ Route('cms.product.edit-sale', ['id' => $product->id]) }}" class="btn btn-outline-primary px-4"
                role="button">取消</a>
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
