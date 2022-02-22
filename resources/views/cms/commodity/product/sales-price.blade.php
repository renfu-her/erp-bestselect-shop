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
            @if ($style->type == 'p')
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
            <h6 class="d-flex align-items-center">售價資訊
                <button id="batch_price" type="button" class="ms-4 btn btn-sm btn-primary">一鍵產生價格</button>
                <a class="ms-2" data-bs-toggle="popover" title="一鍵產生價格" data-bs-trigger="focus" tabindex="0"
                   data-bs-content="以黃底通路為基準，依銷售通路折扣設定批次產生未設定之通路價格（已設定則不更動）"><i class="bi bi-question-circle"></i>
                </a>
            </h6>
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
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="預設：(售價-經銷價) × {{ App\Enums\Customer\Bonus::bonus()->value }}"></i>
                            </th>
                            <th scope="col">喜鴻紅利抵扣</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sales as $styleKey => $sale)
                            <tr @if ($sale->is_master == '1') class="table-warning" @else data-discount="{{ $sale->discount }}" @endif>
                                <th scope="row">
                                    {{ $sale->title }}
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
            const BonusRate = @json(App\Enums\Customer\Bonus::bonus()->value);

            $('input[name="price[]"], input[name="dealer_price[]"]').on('change', function() {
                const $this = $(this);
                sumBonus($this);
            });
            function sumBonus($target) {
                const price = $target.closest('tr').find('input[name="price[]"]').val() || 0;
                const dealer_price = $target.closest('tr').find('input[name="dealer_price[]"]').val() || 0;
                $target.closest('tr').find('input[name="bonus[]"]').val(Math.floor((price - dealer_price) * BonusRate));
            }

            $('#batch_price').on('click', function () {
                const BasePrice = {
                    price: Number($('tr.table-warning input[name="price[]"]').val()),
                    dealer_price: Number($('tr.table-warning input[name="dealer_price[]"]').val()),
                    origin_price: Number($('tr.table-warning input[name="origin_price[]"]').val())
                };
                $(`tr:not(.table-warning) input[name="price[]"],
                   tr:not(.table-warning) input[name="dealer_price[]"],
                   tr:not(.table-warning) input[name="origin_price[]"]`).val(function (index, value) {
                    if (value != '0') {
                        return value;
                    } else {
                        const _name = $(this).attr('name').replace('[]', '');
                        const discount = $(this).closest('tr').data('discount');
                        return Math.round(BasePrice[_name] * discount);
                    }
                });
                sumBonus($('tr:not(.table-warning) input[name="bonus[]"]'));
            });
        </script>
    @endpush
@endOnce
