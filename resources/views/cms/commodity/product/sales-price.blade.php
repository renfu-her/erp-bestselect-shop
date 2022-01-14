@extends('layouts.main')
@section('sub-content')
    <div>
        <h2 class="mb-3">{{ $product->title }}</h2>
        <x-b-prd-navi :product="$product"></x-b-prd-navi>
    </div>
    <div class="card shadow p-4 mb-4">
        <div class="d-flex align-items-center mb-4">
            <h6 class="flex-grow-1 mb-0">商品資訊</h6>
            <a href="{{ Route('cms.product.edit-price', ['id' => $style['product_id'], 'sid' => $style['id']]) }}"
                class="btn btn-outline-primary px-4 -in-header">
                <i class="bi bi-arrow-left-right"></i> 庫存管理
            </a>
        </div>
        <div class="row">
            <fieldset class="col-12 mb-2">
                <legend class="col-form-label">款式</legend>
                <div class="d-flex flex-wrap form-control">
                    @foreach ($style->spec_titles as $title)
                        <span class="badge rounded-pill bg-secondary me-2">{{ $title }}</span>
                    @endforeach
                </div>
            </fieldset>
            <fieldset class="col-12 col-md-6 mb-3">
                <legend class="col-form-label">負責人</legend>
                <div class="form-control">{{ $product->user_name }}</div>
            </fieldset>
            @if ($style->type == 'P')
                @php
                    $suppliers = json_decode($product->suppliers);
                @endphp
                <fieldset class="col-12 col-md-6 mb-2">
                    <legend class="col-form-label">廠商名稱</legend>
                    <div class="d-flex flex-wrap form-control">
                        @foreach ($suppliers as $supplier)
                            <span class="badge rounded-pill bg-secondary me-2">{{ $supplier->name }}</span>
                        @endforeach
                    </div>
                </fieldset>
            @endif
        </div>
    </div>
    <form action="" method="POST">
        @csrf
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
                        @foreach ($sales as $styleKey => $sale)
                            <tr>
                                <th scope="row">{{ $sale->title }}
                                    <input type="hidden" name="sale_channel_id[]" value="{{ $sale->sale_id }}">
                                </th>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" class="form-control form-control-sm" name="price[]" min="0"
                                            value="{{ $sale->price }}" required />
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" class="form-control form-control-sm" name="dealer_price[]"
                                            min="0" value="{{ $sale->dealer_price }}" required />
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" class="form-control form-control-sm" name="origin_price[]"
                                            min="0" value="{{ $sale->origin_price }}" required />
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" class="form-control form-control-sm" name="bonus[]" min="0"
                                            value="{{ $sale->bonus }}" required />
                                    </div>
                                </td>
                                <td>
                                    @if ($sale->is_realtime == 1)
                                        <input type="number" class="form-control form-control-sm" name="dividend[]" min="0"
                                            value="{{ $sale->dividend }}" required>
                                    @else
                                        無法提供
                                        <input type="hidden" name="dividend[]" value="0">
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
                <a href="{{ Route('cms.product.edit-sale', ['id' => $product->id]) }}"
                    class="btn btn-outline-primary px-4" role="button">取消</a>
            </div>
        </div>
    </form>
@endsection
@once
    @push('sub-styles')
        <style>
            fieldset .badge.rounded-pill {
                font-size: .94rem;
                font-weight: 400;
            }

        </style>
    @endpush
    @push('sub-scripts')
        <script>
            // 獎金%數
            const BonusRate = 0.97;

            $('input[name="price[]"], input[name="dealer_price[]"]').on('change', function () {
                const $this = $(this);
                const price = $this.closest('tr').find('input[name="price[]"]').val() || 0;
                const dealer_price = $this.closest('tr').find('input[name="dealer_price[]"]').val() || 0;
                $this.closest('tr').find('input[name="bonus[]"]').val(Math.floor((price - dealer_price) * BonusRate));
            });
        </script>
    @endpush
@endOnce
