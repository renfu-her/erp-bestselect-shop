@extends('layouts.main')
@section('sub-content')
<div>
    <h2 class="mb-3">{{ $product->title }}</h2>
    <x-b-prd-navi :product="$product"></x-b-prd-navi>
</div>
<div class="card shadow p-4 mb-4">
    <h6>商品資訊</h6>
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
<div class="card shadow p-4 mb-4">
    <h6>各通路庫存</h6>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col" style="width:25%;"></th>
                    <th scope="col">安全庫存</th>
                    <th scope="col">可預扣庫存</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">總即時庫存</th>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
            <tfoot class="table-success">
                <tr>
                    <th>實際庫存</th>
                    <td colspan="2" class="text-end pe-4">45</td>
                </tr>
            </tfoot>
        </table>

        <label class="text-muted py-1">即時庫存</label>
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th scope="col" style="width:25%;"></th>
                    <th scope="col">商品訂單預扣</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">官網</th>
                    <td>20</td>
                </tr>
                <tr>
                    <th scope="row">同業網</th>
                    <td>10</td>
                </tr>
            </tbody>
            <tfoot class="table-success">
                <tr>
                    <th>總計</th>
                    <td class="text-end pe-4">30</td>
                </tr>
            </tfoot>
        </table>

        <label class="text-muted py-1">非即時庫存</label>
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th scope="col" style="width:25%;"></th>
                    <th scope="col">預扣庫存</th>
                    <th scope="col">異動數量</th>
                    <th scope="col">預扣試算</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">蝦皮</th>
                    <td>10</td>
                    <td>-5</td>
                    <td>5</td>
                </tr>
                <tr>
                    <th scope="row">美安</th>
                    <td>5</td>
                    <td>5</td>
                    <td>10</td>
                </tr>
            </tbody>
            <tfoot class="table-success">
                <tr>
                    <th>總計試算</th>
                    <td colspan="3" class="text-end pe-4">15</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<div>
    <div class="col-auto">
        <a href="{{ Route('cms.product.edit-price', ['id' => $style['product_id'], 'sid' => $style['id']]) }}"
            class="btn btn-primary px-4">
            <i class="bi bi-arrow-left-right"></i> 價格管理
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
