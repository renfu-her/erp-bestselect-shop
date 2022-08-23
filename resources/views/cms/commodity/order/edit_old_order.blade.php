@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">#{{ $order->sn }} 編輯訂單</h2>

    <form action="{{ route('cms.order.edit-item', ['id' => $order->id]) }}" method="post">
        @csrf
        @foreach ($subOrders as $subOrder)
            <div @class([
                'card shadow mb-4 -detail',
                '-detail-primary' => $subOrder->ship_category === 'deliver',
                '-detail-warning' => $subOrder->ship_category === 'pickup',
            ])>
                <div
                    class="card-header px-4 d-flex align-items-center bg-white flex-wrap justify-content-end border-bottom-0">
                    <strong class="flex-grow-1 mb-0">#{{ $subOrder->sn }}</strong>
                    <strong class="mb-0 mx-2">{{ $subOrder->ship_event }}</strong>
                    <span class="badge -badge fs-6">{{ $subOrder->ship_category_name }}</span>
                </div>
                <div class="card-body px-4 py-0">
                    <div class="table-responsive tableOverBox">
                        <table class="table tableList table-sm table-hover mb-0">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th scope="col">商品名稱-款式</th>
                                    <th scope="col">SKU</th>
                                    <th>售價</th>
                                    <th>經銷價</th>

                                    <th>數量</th>
                                    <th>說明</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subOrder->items as $key => $item)
                                    <input type="hidden" name="item_id[]" value="{{$item->item_id}}">
                                    <tr>
                                        <td>{{ $item->product_title }}
                                            <input type="hidden" name="style_id[]" value="{{ $item->style_id }}">
                                        </td>
                                        <td>{{ $item->sku }}</td>
                                        <td>
                                            <input class="form-control form-control-sm -sx" type="text" aria-label="售價"
                                                value="{{ $item->price }}" disabled>
                                        </td>
                                        <td>
                                            <input class="form-control form-control-sm -sx" type="text" aria-label="經銷價"
                                                value="{{ $item->dealer_price }}" disabled>
                                        </td>

                                        <td class="text-center">
                                            <input class="form-control form-control-sm -sx" type="text" aria-label="數量"
                                                value="{{ $item->qty }}" disabled>
                                        </td>
                                        <td>
                                            <input class="form-control form-control-sm -l" type="text" name="note[]"
                                                aria-label="說明" value="{{ $item->note }}">
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
            <a href="{{ Route('cms.order.detail', ['id' => $order->id]) }}" class="btn btn-outline-primary px-4"
                role="button">返回明細</a>
        </div>
    </form>
@endsection

@once
    @push('sub-styles')
        <link rel="stylesheet" href="{{ Asset('dist/css/order.css') }}">
        <style>
        </style>
    @endpush
    @push('sub-scripts')
        <script></script>
    @endpush
@endonce
