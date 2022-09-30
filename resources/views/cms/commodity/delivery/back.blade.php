@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $breadcrumb_data['sn'] }} 退貨審核</h2>
    @if ($event === 'consignment')
        <x-b-consign-navi :id="$delivery->event_id"></x-b-consign-navi>
    @endif
    @if ($event === 'csn_order')
        <x-b-csnorder-navi :id="$delivery->event_id"></x-b-csnorder-navi>
    @endif
    @error('error_msg')
    <div class="alert alert-danger" role="alert">
        {{ $message }}
    </div>
    @enderror
    @error('item_error')
    <div class="alert alert-danger" role="alert">
        {{ $message }}
    </div>
    @enderror
    
    <form method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>訂單退貨單內容</h6>
            <div class="col-12 mb-3">
                <label class="form-label">退貨單備註</label>
                <input class="form-control" type="text" value="{{$delivery->back_memo ?? ''}}" name="dlv_memo" placeholder="退貨單備註">
            </div>
            <div class="table-responsive tableOverBox">
                <table id="Pord_list" class="table table-striped tableList">
                    <thead>
                        <tr>
                            <th style="width:3rem;">#</th>
                            <th>商品名稱</th>
                            <th>SKU</th>
                            <th>價格</th>
                            <th>原數量</th>
                            <th class="text-center" style="width: 10%">欲退數量</th>
                            <th>說明</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (null != $ord_items)
                            @foreach ($ord_items as $key => $item)
                                <tr class="--prod">
                                    <th scope="row">{{ $key + 1 }}
                                        <input type="hidden" name="id[]" value="{{ $item->id ?? '' }}" />
                                        <input type="hidden" name="event_item_id[]" value="{{ $item->event_item_id ?? '' }}" />
                                        <input type="hidden" name="product_style_id[]" value="{{ $item->product_style_id ?? '' }}" />
                                        <input type="hidden" name="product_title[]" value="{{ $item->product_title ?? '' }}" />
                                        <input type="hidden" name="sku[]" value="{{ $item->sku ?? '' }}" />
                                        <input type="hidden" name="price[]" value="{{ $item->price ?? '' }}" />
                                        <input type="hidden" name="origin_qty[]" value="{{ $item->origin_qty ?? '' }}" />
                                    </th>
                                    <td>{{ $item->product_title }}</td>
                                    <td>{{ $item->sku }}</td>
                                    <td>${{ number_format($item->price) }}</td>
                                    <td>{{ $item->origin_qty ? number_format($item->origin_qty) : '' }}</td>
                                    <td>
                                        <x-b-qty-adjuster name="back_qty[]" value="{{ $item->back_qty ?? 0 }}"
                                            min="0" max="{{ $item->origin_qty ?? '' }}" 
                                            size="sm" minus="-" plus="+"></x-b-qty-adjuster>
                                    </td>
                                    <td>
                                        <input type="text" value="{{ $item->memo ?? '' }}" name="memo[]" class="form-control form-control-sm -l">
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            @error('error_msg')
                <div class="alert alert-danger" role="alert">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div id="submitDiv">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4" >送出</button>
                @if($delivery->event == App\Enums\Delivery\Event::order()->value)
                    <a href="{{ Route('cms.order.detail', ['id' => $order_id, 'subOrderId' => $eventId ]) }}" class="btn btn-outline-primary px-4" role="button">返回明細</a>
                @elseif($delivery->event == App\Enums\Delivery\Event::consignment()->value)
                    <a href="{{ Route('cms.consignment.edit', ['id' => $eventId ]) }}" class="btn btn-outline-primary px-4" role="button">返回明細</a>
                @elseif($delivery->event == App\Enums\Delivery\Event::csn_order()->value)
                    <a href="{{ Route('cms.consignment-order.edit', ['id' => $eventId ]) }}" class="btn btn-outline-primary px-4" role="button">返回明細</a>
                @endif
            </div>
        </div>
    </form>

@endsection
@once
    @push('sub-scripts')
        <script>
            // +/- btn
            $('button.-minus, button.-plus').on('click', function() {
                const $input = $(this).siblings('input[type="number"]');
                const max = $input.attr('max') !== '' ? Number($input.attr('max')) : null;
                const min = $input.attr('min') !== '' ? Number($input.attr('min')) : null;
                const m_qty = Number($input.val());
                if ($(this).hasClass('-minus') && (min !== null && m_qty > min)) {
                    $input.val(m_qty - 1);
                }
                if ($(this).hasClass('-plus') && (max != null && m_qty < max)) {
                    $input.val(m_qty + 1);
                }
            });
        </script>
    @endpush
@endonce
