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
        <fieldset class="col-12 mb-3">
            <legend class="col-form-label">款式</legend>
            <div class="form-control">S、R</div>
        </fieldset>
        <fieldset class="col-12 col-md-6 mb-3">
            <legend class="col-form-label">負責人</legend>
            <div class="form-control">施欽元</div>
        </fieldset>
        <fieldset class="col-12 col-md-6 mb-3">
            <legend class="col-form-label">廠商名稱</legend>
            <div class="form-control">BANNIES</div>
        </fieldset>
    </div>
</div>
<form action="" method="POST">
    <div class="card shadow p-4 mb-4">
        <h6>售價資訊</h6>
        <div class="table-responsive tableOverBox">
            <table class="table tableList table-hover mb-1">
                <thead>
                    <tr>
                        <th scope="col">銷售通路</th>
                        <th scope="col">售價</th>
                        <th scope="col">經銷價</th>
                        <th scope="col">定價</th>
                        {{-- <th scope="col">預估成本</th> --}}
                        <th scope="col">獎金
                            <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="預設：(售價-經銷價) × 0.97"></i>
                        </th>
                        <th scope="col">喜鴻紅利抵扣</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @foreach ($styles as $styleKey => $style) --}}
                        <tr>
                            <th scope="row">蝦皮
                                <input type="hidden" name="sale_channel_id[]" value="">
                            </th>
                            <td>
                                <div class="input-group input-group-sm flex-nowrap">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="number" class="form-control form-control-sm" name="price[]" min="0" value="" required/>
                                </div>
                            </td>
                            <td>
                                <div class="input-group input-group-sm flex-nowrap">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="number" class="form-control form-control-sm" name="dealer_price[]" min="0" value="" required/>
                                </div>
                            </td>
                            <td>
                                <div class="input-group input-group-sm flex-nowrap">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="number" class="form-control form-control-sm" name="origin_price[]" min="0" value="" required/>
                                </div>
                            </td>
                            <td>
                                <div class="input-group input-group-sm flex-nowrap">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="number" class="form-control form-control-sm" name="bonus[]" min="0" value="" required/>
                                </div>
                            </td>
                            <td>無法提供</td>
                        </tr>
                        <tr>
                            <th scope="row">官網
                                <input type="hidden" name="sale_channel_id[]" value="">
                            </th>
                            <td>
                                <div class="input-group input-group-sm flex-nowrap">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="number" class="form-control form-control-sm" name="price[]" min="0" value="" required/>
                                </div>
                            </td>
                            <td>
                                <div class="input-group input-group-sm flex-nowrap">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="number" class="form-control form-control-sm" name="dealer_price[]" min="0" value="" required/>
                                </div>
                            </td>
                            <td>
                                <div class="input-group input-group-sm flex-nowrap">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="number" class="form-control form-control-sm" name="origin_price[]" min="0" value="" required/>
                                </div>
                            </td>
                            <td>
                                <div class="input-group input-group-sm flex-nowrap">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="number" class="form-control form-control-sm" name="bonus[]" min="0" value="" required/>
                                </div>
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm" name="dividend[]" min="0" value="" required>
                            </td>
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
