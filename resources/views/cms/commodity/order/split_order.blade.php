@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">#{{ $order->sn }} 分割訂單</h2>

    <form action="{{ route('cms.order.split-order', ['id' => $order->id]) }}" method="post">
        @csrf
        @foreach ($subOrders as $subOrder)
            <div @class([
                'card shadow mb-4 -detail',
                '-detail-primary' => $subOrder->ship_category === 'deliver',
                '-detail-warning' => $subOrder->ship_category === 'pickup',
            ])>
                <div
                    class="card-header px-4 d-flex align-items-center bg-white flex-wrap justify-content-end border-bottom-0">
                    <strong class="flex-grow-1 mb-0">{{ $subOrder->ship_event }}</strong>
                    <span class="badge -badge fs-6">{{ $subOrder->ship_category_name }}</span>
                </div>
                <div class="card-body px-4 py-0">
                    <div class="table-responsive tableOverBox">
                        <table class="table tableList table-sm table-hover mb-0">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th scope="col" style="width:10%;">選取</th>
                                    <th scope="col">商品名稱</th>
                                    <th scope="col" style="width:20%;">SKU</th>
                                    <th scope="col" style="width:10%;" class="text-center">訂購數量</th>
                                    <th scope="col" style="width:10%;" class="text-center">分出數量</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subOrder->items as $key => $item)
                                    <tr>
                                        <th>
                                            <input class="form-check-input ms-1" name="style_id[]"
                                                value="{{ $item->style_id }}" type="checkbox">
                                        </th>
                                        <td>{{ $item->product_title }}</td>
                                        <td>{{ $item->sku }}</td>
                                        <td class="text-center">{{ $item->qty }}</td>
                                        <td class="text-center">
                                            <select name="qty[]" class="form-select form-select-sm" disabled
                                                aria-label="分出數量">
                                                @for ($i = 1; $i <= $item->qty; $i++)
                                                    <option value="{{ $i }}" @if ($i == $item->qty) selected @endif>
                                                        {{ $i }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach


        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">送出</button>
            <a href="{{ Route('cms.order.detail', ['id' => $order->id]) }}" class="btn btn-outline-primary px-4" role="button">返回明細</a>
        </div>
    </form>
@endsection
@once
    @push('sub-styles')
        <link rel="stylesheet" href="{{ Asset('dist/css/order.css') }}">
        <style>
            .table.table-bordered:not(.table-sm) tr:not(.table-light) {
                height: 70px;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            $('input[name="style_id[]"]').on('change', function() {
                const check = $(this).prop('checked');
                $(this).closest('tr').find('select[name="qty[]"]').prop('disabled', !check);
            });
        </script>
    @endpush
@endonce
