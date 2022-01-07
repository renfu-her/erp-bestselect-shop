@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">[{{ $product->title }}] {{ $style->title }}</h2>
    <form action="{{ Route('cms.combo-purchase.edit', ['id' => $style->id], true) }}" method="POST">
        @csrf
        <div class="card shadow p-4 mb-4">
            <p class="mb-4">當前庫存：<span class="text-decoration-underline fs-4">{{ $style->in_stock }}</span>（組）</p>

            <div class="row justify-content-center mb-3">
                <label class="text-muted text-center">數量異動</label>
                <div class="col-auto mb-3">
                    <div class="input-group input-group-lg has-validation">
                        <button class="btn btn-danger -minus" type="button" data-bs-toggle="tooltip" title="拆包">
                            <i class="bi bi-dash-lg"></i>
                        </button>
                        <input type="number" class="form-control text-center  @error('status') is-invalid  @enderror"
                            name="qty" value="0" min="-{{ $style->in_stock }}">
                        <button class="btn btn-success -plus" type="button" data-bs-toggle="tooltip" title="組裝">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                        <div class="invalid-feedback">
                            @error('status')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
            </div>


            <div class="table-responsive">
                <table class="table table-striped tableList">
                    <thead>
                        <tr>
                            <th scope="col" style="width:10%">#</th>
                            <th scope="col">SKU</th>
                            <th scope="col">商品名稱</th>
                            <th scope="col">款式</th>
                            <th scope="col">數量</th>
                            <th scope="col" class="text-center border-start border-end">目前庫存</th>
                            <th scope="col" class="text-center">剩餘庫存試算</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($combos as $key => $combo)
                            <tr>
                                <th scope="row">{{ $key + 1 }}</th>
                                <td>{{ $combo->sku }}</td>
                                <td>{{ $combo->title }}</td>
                                <td>{{ $combo->spec }}</td>
                                <td data-td="qty">{{ $combo->qty }}</td>
                                <td data-td="stock" class="text-center border-start border-end fw-bold fs-5">5</td>
                                <td data-td="count" class="text-center fs-5"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
            .border-start.border-end {
                border-left-color: black !important;
                border-right-color: black !important;
            }

        </style>
    @endpush
    @push('sub-scripts')
        <script>
            const min_stock = Number($('input[name="qty"]').attr('min'));
            countStock();
            // 數量異動 input
            $('input[name="qty"]').on('change', function() {
                countStock();
            });
            // +/- btn
            $('button.-minus, button.-plus').on('click', function() {
                const m_qty = Number($('input[name="qty"]').val());
                if ($(this).hasClass('-minus') && m_qty > min_stock) {
                    $('input[name="qty"]').val(m_qty - 1);
                }
                if ($(this).hasClass('-plus')) {
                    $('input[name="qty"]').val(m_qty + 1);
                }
                countStock();
            });

            function countStock() {
                const m_qty = Number($('input[name="qty"]').val());

                $('tbody td[data-td="qty"]').each(function(index, element) {
                    // element == this
                    const qty = Number($(element).text());
                    const stock = Number($(element).siblings('td[data-td="stock"]').text());
                    if (isFinite(m_qty) && isFinite(qty) && isFinite(stock)) {
                        const remainder = stock - (qty * m_qty);
                        $(element).siblings('td[data-td="count"]').text(remainder);
                        if (remainder < 0) {
                            $(element).siblings('td[data-td="count"]').removeClass('text-primary').addClass(
                                'text-danger');
                            $('form button[type="submit"]').prop('disabled', true);
                        } else {
                            $(element).siblings('td[data-td="count"]').removeClass('text-danger').addClass(
                                'text-primary');
                            $('form button[type="submit"]').prop('disabled', false);
                        }
                    }
                });

                if (m_qty < min_stock) {
                    $('form button[type="submit"]').prop('disabled', true);
                }
            }
        </script>
    @endpush
@endonce
