@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">{{ $product->title }}
        <span class="small"><span class="badge bg-secondary">{{ $style->title }}</span></span>
    </h2>
    <form action="{{ Route('cms.combo-purchase.edit', ['id' => $style->id], true) }}" method="POST">
        @csrf
        <div class="card shadow p-4 mb-4">
            <p class="mb-4">當前可售數量：<span class="text-decoration-underline fs-4">{{ $style->in_stock }}</span>（組）</p>
            @php
                $s_min = $style->in_stock > 0 ? -$style->in_stock : 0;
            @endphp
            <div class="row justify-content-center mb-3">
                <label class="text-muted text-center">數量異動</label>
                <div class="col-auto mb-3">
                    <x-b-qty-adjuster name="qty" min="{{ $s_min }}" size="lg" minus="拆包" plus="組裝"></x-b-qty-adjuster>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped tableList">
                    <thead class="align-middle">
                        <tr>
                            <th scope="col" style="width:40px">#</th>
                            <th scope="col">SKU</th>
                            <th scope="col">商品名稱</th>
                            <th scope="col">款式</th>
                            <th scope="col" class="text-center border-end">數量</th>
                            <th scope="col" class="text-center small wrap border-end">元素被組合可售數量</th>
                            <th scope="col" class="text-center small wrap border-end">目前可售數量</th>
                            <th scope="col" class="text-center small wrap">剩餘可售數量試算</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($combos as $key => $combo)
                            <tr>
                                <th scope="row">{{ $key + 1 }}</th>
                                <td>{{ $combo->sku }}</td>
                                <td class="wrap">{{ $combo->title }}</td>
                                <td>{{ $combo->spec }}</td>
                                <td data-td="qty" class="text-center border-end">{{ $combo->qty }}</td>
                                <td data-td="qty" class="text-center border-end fs-5">{{ $combo->qty * $style->in_stock }}</td>
                                <td data-td="stock" class="text-center border-end fw-bold fs-5">{{ $combo->in_stock }}</td>
                                <td data-td="count" class="text-center fs-5 pe-0">{{ $combo->in_stock }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>
                <div class="form-check form-check-inline">
                    <label class="form-check-label">
                        <input class="form-check-input" name="check_stock" type="checkbox" checked>
                        限制<span class="text-primary">剩餘庫存</span>不為負
                    </label>
                </div>
            </div>
        </div>

        <div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
                <a href="{{ Route('cms.combo-purchase.index') }}" class="btn btn-outline-primary px-4"
                    role="button">返回列表</a>
            </div>
        </div>
    </form>

@endsection
@once
    @push('sub-styles')
        <style>
            .border-end {
                border-left-color: black !important;
                border-right-color: black !important;
            }

        </style>
    @endpush
    @push('sub-scripts')
        <script>
            const min_stock = @json($s_min);

            // 數量異動 input
            $('input[name="qty"]').on('change', function() {
                countStock();
            });
            // +/- btn
            $('button.-minus, button.-plus').on('click', function() {
                const m_qty = Number($('input[name="qty"]').val());
                if ($(this).hasClass('-minus') &&
                    (!$('input[name="check_stock"]').prop('checked') ||
                    m_qty > min_stock)) {
                    $('input[name="qty"]').val(m_qty - 1);
                }
                if ($(this).hasClass('-plus')) {
                    $('input[name="qty"]').val(m_qty + 1);
                }
                countStock();
            });
            // 負數檢查
            $('input[name="check_stock"]').on('change', function () {
                if ($(this).prop('checked')) {
                    $('input[name="qty"]').attr('min', min_stock);
                    if (Number($('input[name="qty"]').val()) < min_stock) {
                        $('input[name="qty"]').val(min_stock);
                    }
                    countStock();
                } else {
                    $('form button[type="submit"]').prop('disabled', false);
                    $('input[name="qty"]').removeAttr('min');
                }
            });

            function countStock() {
                const m_qty = Number($('input[name="qty"]').val());
                let checkCount = true;

                $('tbody td[data-td="qty"]').each(function(index, element) {
                    // element == this
                    const qty = Number($(element).text());
                    const stock = Number($(element).siblings('td[data-td="stock"]').text());
                    if (isFinite(m_qty) && isFinite(qty) && isFinite(stock)) {
                        const remainder = stock - (qty * m_qty);
                        $(element).siblings('td[data-td="count"]').text(remainder);
                        if (remainder < 0) {
                            $(element).siblings('td[data-td="count"]').removeClass('text-primary')
                                .addClass('text-danger');
                            checkCount &= false;
                        } else {
                            $(element).siblings('td[data-td="count"]').removeClass('text-danger')
                                .addClass('text-primary');
                        }
                    }
                });

                if ($('input[name="check_stock"]').prop('checked') && (m_qty < min_stock || !checkCount)) {
                    $('form button[type="submit"]').prop('disabled', true);
                } else {
                    $('form button[type="submit"]').prop('disabled', false);
                }
            }
        </script>
    @endpush
@endonce

