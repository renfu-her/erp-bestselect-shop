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
                <i class="bi bi-arrow-left-right"></i> 價格管理
            </a>
        </div>
        <div class="row">
            <fieldset class="col-12 mb-2">
                <legend class="col-form-label">款式</legend>
                <div class="d-flex flex-wrap">
                    <span class="form-control col-auto me-2 mb-2">{{ $style->title }}</span>
                </div>
            </fieldset>
            <fieldset class="col-12 col-md-6 mb-3">
                <legend class="col-form-label">負責人</legend>
                <div class="form-control">&nbsp;</div>
            </fieldset>
            <fieldset class="col-12 col-md-6 mb-3">
                <legend class="col-form-label">廠商名稱</legend>
                <div class="form-control">&nbsp;</div>
            </fieldset>
        </div>
    </div>
    <form action="{{ route('cms.product.edit-stock', ['id' => $product->id, 'sid' => $style->id]) }}" method="POST">
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>各通路庫存</h6>
            @error('status')
                <div class="alert alert-danger" role="alert">
                    {{ $message }}
                </div>
            @enderror

            <div class="table-responsive">
                <span class="badge -step mb-2">總即時庫存</span>
                <table class="table table-bordered border-dark align-middle mb-4">
                    <tbody>
                        <tr>
                            <th scope="row" style="width:40%;">安全庫存
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="當庫存量少於等於此數量時會提示通知"></i>
                            </th>
                            <td>
                                <input type="number" name="safety_stock" value="{{ $style->safety_stock }}" placeholder=""
                                    min="0" class="form-control form-control-sm">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">預購(超賣)數量
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="當庫存不足時，尚可訂購的數量"></i>
                            </th>
                            <td>
                                <input type="number" name="overbought" value="{{ $style->overbought }}" placeholder=""
                                    min="0" class="form-control form-control-sm">
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>實際庫存
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="right"
                                    title="倉庫剩餘庫存"></i>
                            </th>
                            <th class="text-end pe-4 text-primary -remaining">{{ $style->in_stock }}</th>
                        </tr>
                    </tfoot>
                </table>
                <hr>
                
                <label class="text-secondary py-1 fw-bold">非即時庫存</label>
                <table id="Non_instant" class="table table-bordered border-secondary align-middle mb-4">
                    <thead>
                        <tr>
                            <th scope="col"></th>
                            <th scope="col">預扣庫存
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="將庫存預扣給該通路，會從實際庫存扣除"></i>
                            </th>
                            <th scope="col" style="width:32%;">異動數量
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="調整預扣庫存數量"></i>
                            </th>
                            <th scope="col">預扣試算</th>
                        </tr>
                    </thead>
                    <tbody class="border-secondary">
                        @php
                            $sum = 0;
                        @endphp
                        @foreach ($stocks as $key => $stock)
                            @php
                                $sum += $stock->in_stock;
                            @endphp
                            <tr>
                                <th scope="row">{{ $stock->title }}</th>
                                <td data-td="stock">{{ $stock->in_stock }}</td>
                                <td data-td="qty">
                                    <x-b-qty-adjuster name="qty[]" min="-{{ $stock->in_stock }}"
                                        max="{{ $style->in_stock }}" size="sm" minus="減少" plus="增加">
                                    </x-b-qty-adjuster>
                                </td>
                                <td data-td="count" class="text-primary">{{ $stock->in_stock }}</td>
                                <input type="hidden" name="sale_id[]" value={{ $stock->sale_id }}>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-secondary">
                        <tr>
                            <th>總計試算</th>
                            <td>{{ $sum }}</td>
                            <td colspan="2" class="table-warning border-secondary text-end pe-4 -sum">{{ $sum }}</td>
                        </tr>
                    </tfoot>
                </table>

                <label class="text-secondary py-1 fw-bold">即時庫存</label>
                <table class="table table-bordered border-secondary align-middle">
                    <thead>
                        <tr>
                            <th scope="col" style="width:40%;"></th>
                            <th scope="col">訂購未付款
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="right"
                                    title="已有訂單但尚未付款的訂購數量"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="border-secondary">
                        <tr>
                            <th scope="row">官網</th>
                            <td>20</td>
                        </tr>
                        <tr>
                            <th scope="row">同業網</th>
                            <td>10</td>
                        </tr>
                    </tbody>
                    <tfoot class="border-secondary">
                        <tr>
                            <th>總計</th>
                            <td>30</td>
                        </tr>
                    </tfoot>
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
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            // 實際庫存
            const Old_stock = @json($style->in_stock);
            
            // 數量異動 input
            $('input[name="qty[]"]').on('change', function() {
                countStock();
            });
            // +/- btn
            $('button.-minus, button.-plus').on('click', function() {
                const $qty = $(this).siblings('input[name="qty[]"]');
                const min = Number($qty.attr('min'));
                const max = Number($qty.attr('max'));
                const m_qty = Number($qty.val());
                if ($(this).hasClass('-minus') && m_qty > min) {
                    $qty.val(m_qty - 1);
                }
                if ($(this).hasClass('-plus') && m_qty < max) {
                    $qty.val(m_qty + 1);
                }
                countStock();
            });

            function countStock() {
                let checkCount = true;
                let sum = 0;
                let total_qty = 0;

                $('tbody td[data-td="stock"]').each(function(index, element) {
                    // element == this
                    const stock = Number($(element).text());    // 預扣庫存
                    const $qty = $(element).siblings('td[data-td="qty"]').find('input[name="qty[]"]');
                    const qty = Number($qty.val());     // 異動數量
                    const min = Number($qty.attr('min'));
                    const max = Number($qty.attr('max'));
                    if (isFinite(qty) && isFinite(stock)) {
                        const remainder = stock + qty;
                        $(element).siblings('td[data-td="count"]').text(remainder);
                        total_qty += qty;
                        sum += remainder;
                        if (remainder < 0) {
                            $(element).siblings('td[data-td="count"]').removeClass('text-primary')
                                .addClass('text-danger');
                            checkCount &= false;
                        } else {
                            $(element).siblings('td[data-td="count"]').removeClass('text-danger')
                                .addClass('text-primary');
                        }

                        // check max/min
                        if (max && qty > max) {
                            checkCount &= false;
                        }
                        if (min && qty < min) {
                            checkCount &= false;
                        }
                    }
                });
                $('#Non_instant .-sum').text(sum);

                // 計算實際庫存
                const New_stock = Old_stock - total_qty;
                $('th.-remaining').text(New_stock);
                $('input[name="qty[]"]').attr('max', function(index, attr) {
                    return Number($('input[name="qty[]"]')[index].value) + New_stock;
                });
                if (New_stock < 0) {
                    $('th.-remaining').removeClass('text-primary').addClass('text-danger');
                    checkCount &= false;
                } else {
                    $('th.-remaining').removeClass('text-danger').addClass('text-primary');
                }

                if (checkCount) {
                    $('form button[type="submit"]').prop('disabled', false);
                } else {
                    $('form button[type="submit"]').prop('disabled', true);
                }
            }
        </script>
    @endpush
@endOnce
