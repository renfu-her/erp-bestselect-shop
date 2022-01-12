@extends('layouts.main')
@section('sub-content')
<div>
    <h2 class="mb-3">{{ $product->title }}</h2>
    <x-b-prd-navi :product="$product"></x-b-prd-navi>
</div>
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
        <a href="{{ Route('cms.product.edit-stock', ['id' => $style['product_id'], 'sid' => $style['id']]) }}"
            class="btn btn-primary px-4">
            <i class="bi bi-arrow-left-right"></i> 庫存管理
        </a>
        <a href="{{ Route('cms.product.edit-sale', ['id' => $product->id]) }}" class="btn btn-outline-primary px-4"
            role="button">取消</a>
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
