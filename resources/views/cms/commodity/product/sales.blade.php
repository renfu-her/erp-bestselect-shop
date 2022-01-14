@extends('layouts.main')
@section('sub-content')
<div>
    <h2 class="mb-3">{{ $product->title }}</h2>
    <x-b-prd-navi :product="$product"></x-b-prd-navi>
</div>
<div class="card shadow p-4 mb-4">
    <h6>銷售控管</h6>
    <div class="table-responsive tableOverBox">
        <table class="table tableList table-striped mb-1">
            <thead>
                <tr>
                    <th scope="col" class="text-center">庫存管理</th>
                    <th scope="col" class="text-center">價格管理</th>
                    <th scope="col">規格</th>
                    <th scope="col">SKU</th>
                   
                    <th scope="col">庫存</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($styles as $styleKey => $style)
                    <tr>
                        <td class="text-center">
                            <a href="{{ Route('cms.product.edit-stock', ['id' => $style['product_id'], 'sid' => $style['id']]) }}"
                                class="icon -del icon-btn fs-5 text-primary rounded-circle border-0 p-0">
                                <i class="bi bi-box-seam"></i>
                            </a>
                        </td>
                        <td class="text-center">
                            <a href="{{ Route('cms.product.edit-price', ['id' => $style['product_id'], 'sid' => $style['id']]) }}"
                                class="icon -del icon-btn fs-5 text-primary rounded-circle border-0 p-0">
                                <i class="bi bi-tags"></i>
                            </a>
                        </td>
                        <td>{{ $style['title'] }}</td>
                        <td>{{ $style['sku'] }}</td>          
                        <td>{{ $style['in_stock'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div>
    <div class="col-auto">
        <a href="{{ Route('cms.product.index') }}" class="btn btn-outline-primary px-4" role="button">返回列表</a>
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
