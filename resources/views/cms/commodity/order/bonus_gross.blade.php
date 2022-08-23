@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">獎金毛利</h2>

    <ul class="nav nav-tabs border-bottom-0">
        <li class="nav-item">
            <button class="nav-link -page1 active" aria-current="page" type="button">毛利資訊</button>
        </li>
        <li class="nav-item">
            <button class="nav-link -page2" type="button">修改紀錄</button>
        </li>
    </ul>

    <div id="page1">
        <div class="card shadow p-4 mb-4">
            @if (count($discounts) > 0)
                <h6>折扣明細</h6>
                <div class="table-responsive">
                    <table class="table table-sm text-right align-middle">
                        <tbody>
                            @foreach ($discounts as $key => $dis)
                                <tr>
                                    @switch($dis->category_code)
                                        @case('code')
                                        @case('coupon')
                                            <td class="col-8">{{ $dis->category_title }}【{{ $dis->title }}】</td>
                                        @break

                                        @default
                                            <td class="col-8">{{ $dis->title }}</td>
                                    @endswitch

                                    @if ($dis->method_code == 'coupon')
                                        <td class="text-end pe-4">【{{ $dis->extra_title }}】</td>
                                    @elseif (is_numeric($dis->discount_value))
                                        <td class="text-end pe-4 text-danger">-
                                            ${{ number_format($dis->discount_value) }}</td>
                                    @else
                                        <td class="text-end pe-4">{{ $dis->discount_value || '' }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <h6 class="mb-1">訂單總覽</h6>
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle d-sm-table d-none text-nowrap mb-1 caption-top">
                    @if (!$order->allotted_dividend)
                        <caption class="small text-end py-0">鴻利預計發放時間：
                            @if (isset($order->dividend_active_at))
                                {{ date('Y/m/d H:i', strtotime($order->dividend_active_at)) }}
                            @else
                                未入款
                            @endif
                        </caption>
                    @endif

                    <tbody class="border-top-0">
                        <tr class="table-warning">
                            <td class="col-2">小計</td>
                            <td class="col-2">折扣</td>
                            <td class="col-2 lh-sm">折扣後 <br class="d-xxl-none">(不含運)</td>
                            <td class="col-2">運費</td>
                            <td class="col-2">總金額</td>
                            <td class="col-2 lh-sm">
                                @if ($order->allotted_dividend)
                                    獲得<a href="{{ route('cms.sale_channel.index') }}" class="-text">鴻利</a>
                                @else
                                    預計獲得<a href="{{ route('cms.sale_channel.index') }}"
                                        class="-text d-block d-xxl-inline">鴻利點數</a>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>${{ number_format($order->origin_price) }}</td>
                            <td class="text-danger">- ${{ number_format($order->discount_value) }}</td>
                            <td>${{ number_format($order->discounted_price) }}</td>
                            <td>${{ number_format($order->dlv_fee) }}</td>
                            <td class="fw-bold">${{ number_format($order->total_price) }}</td>
                            <td>{{ number_format($dividend) }}
                                @if ($order->allotted_dividend)
                                    <span class="badge bg-success">已發</span>
                                @else
                                    <span class="badge bg-secondary">未發</span>
                                @endif
                            </td>
                        </tr>

                        {{-- 總獎金 --}}
                        <tr class="table-warning">
                            <td colspan="3">當代總獎金</td>
                            <td colspan="3">上代總獎金</td>
                        </tr>
                        <tr>
                            <td colspan="3">${{ number_format($bonus[0]) }}</td>
                            <td colspan="3">${{ number_format($bonus[1]) }}</td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-bordered table-sm text-center align-middle d-table d-sm-none mb-0">
                    @if (!$order->allotted_dividend)
                        <caption class="small text-end pb-0">鴻利預計發放時間：
                            @if (isset($order->dividend_active_at))
                                {{ date('Y/m/d H:i', strtotime($order->dividend_active_at)) }}
                            @else
                                未入款
                            @endif
                        </caption>
                    @endif
                    <tbody class="border-top-0">
                        <tr style="border-color: #dfe0e1;">
                            <td class="col-7 table-warning">小計</td>
                            <td class="text-end pe-4">${{ number_format($order->origin_price) }}</td>
                        </tr>
                        <tr>
                            <td class="col-7 table-warning">折扣 </td>
                            <td class="text-danger text-end pe-4">- ${{ number_format($order->discount_value) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="col-7 table-warning lh-sm">折扣後 (不含運)</td>
                            <td class="text-end pe-4">${{ number_format($order->discounted_price) }}</td>
                        </tr>
                        <tr>
                            <td class="col-7 table-warning">運費</td>
                            <td class="text-end pe-4">${{ number_format($order->dlv_fee) }}</td>
                        </tr>
                        <tr>
                            <td class="col-7 table-warning">總金額</td>
                            <td class="fw-bold text-end pe-4">${{ number_format($order->total_price) }}</td>
                        </tr>
                        <tr>
                            <td class="col-7 table-warning lh-sm">
                                @if ($order->allotted_dividend)
                                    獲得<a href="{{ route('cms.sale_channel.index') }}" class="-text">鴻利</a>
                                @else
                                    預計獲得<a href="{{ route('cms.sale_channel.index') }}" class="-text">鴻利點數</a>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if ($order->allotted_dividend)
                                    <span class="badge bg-success">已發</span>
                                @else
                                    <span class="badge bg-secondary">未發</span>
                                @endif
                                {{ number_format($dividend) }}
                            </td>
                        </tr>

                        {{-- 總獎金 --}}
                        <tr>
                            <td class="col-7 table-warning lh-sm">當代總獎金</td>
                            <td class="text-end pe-4">${{ number_format($bonus[0]) }}</td>
                        </tr>
                        <tr>
                            <td class="col-7 table-warning lh-sm">上代總獎金</td>
                            <td class="text-end pe-4">${{ number_format($bonus[1]) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow p-4 mb-4">
            <h6>毛利資訊</h6>
            <div class="table-responsive tableOverBox mb-3">
                <table class="table tableList table-striped mb-1">
                    <thead>
                        <tr>
                            <th scope="col" style="width:40px">#</th>
                            <th scope="col">子訂單</th>
                            <th scope="col">品名規格</th>
                            <th scope="col">銷售通路</th>
                            <th scope="col" class="text-center px-3">金額</th>
                            <th scope="col" class="text-center px-3">經銷價</th>
                            <th scope="col" class="text-center px-3">商品成本</th>
                            <th scope="col" class="text-center px-3">數量</th>
                            <th scope="col" class="text-center px-3">小計</th>
                            <th scope="col" class="text-center px-3">毛利</th>
                            <th scope="col" class="text-center px-3">出庫數量</th>
                            <th scope="col">倉庫</th>
                            <th scope="col">入庫單號</th>
                            <th scope="col">產品人員</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataList as $key => $item)
                            <tr>
                                <th scope="row">{{ $key + 1 }}</th>
                                <td>{{ $item->sub_order_sn }}</td>
                                <td>{{ $item->product_title }}</td>
                                <td>{{ $item->channel_title }}</td>
                                <td class="text-center">$ {{ number_format($item->price) }}</td>
                                <td class="text-center">$ {{ number_format(0) }}</td>
                                <td class="text-center">$ {{ number_format($item->unit_cost) * $item->qty }}</td>
                                <td class="text-center">{{ number_format($item->qty) }}</td>
                                <td class="text-center">$ {{ number_format($item->origin_price) }}</td>
                                <td class="text-center">$ {{ number_format(0) }}</td>
                                <td class="text-center">{{ number_format(0) }}</td>
                                <td>-</td>
                                <td>-</td>
                                <td>{{ $item->product_user }}</td>
                            </tr>
                            @if ($item->profit_id)
                                <tr>
                                    <td></td>
                                    <td class="py-0" colspan="11">

                                        <form action="">
                                            <table class="table table-bordered table-sm mb-0">
                                                <tbody>
                                                    <tr class="border-top-0 table-light">
                                                        <td style="width: 10%;font-weight:500;" class="text-center">總獎金
                                                        </td>
                                                        <td style="width: 20%;font-weight:500;" class="text-center">當代推薦人
                                                        </td>
                                                        <td style="width: 20%;font-weight:500;" class="text-center">當代獎金
                                                        </td>
                                                        <td style="width: 20%;font-weight:500;" class="text-center">上代推薦人
                                                        </td>
                                                        <td style="width: 20%;font-weight:500;" class="text-center">上代獎金
                                                        </td>
                                                        <td style="width: 10%" class="text-center">操作</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center">$
                                                            {{ number_format($item->total_bonus) }}
                                                        </td>
                                                        <td class="text-center">

                                                            {{ $item->re_customer }}

                                                        </td>
                                                        <td>
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text">$</span>
                                                                <input type="number" class="form-control text-center"
                                                                    aria-label="當代獎金" name="bonus1"
                                                                    value="{{ $item->bonus }}" min="0"
                                                                    max="{{ $item->total_bonus }}" disabled>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            @if ($item->re_customer2)
                                                                {{ $item->re_customer2 }}
                                                            @else
                                                                無
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if ($item->re_customer2)
                                                                <div class="input-group input-group-sm">
                                                                    <span class="input-group-text">$</span>
                                                                    <input type="text" class="form-control text-center"
                                                                        aria-label="上代獎金" name="bonus2"
                                                                        value="{{ $item->bonus2 }}" disabled readonly>
                                                                </div>
                                                            @else
                                                                無
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <input type="hidden" name="profit_id"
                                                                value="{{ $item->profit_id }}">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-primary -edit px-4 me-0">修改</button>
                                                            <button type="button"
                                                                class="btn btn-sm btn-success -save px-4" hidden
                                                                disabled>儲存</button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </form>

                                    </td>
                                    <td></td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="page2" hidden>
        <div class="card shadow p-4 mb-4">
            <div class="table-responsive tableOverBox">
                <table class="table tableList table-hover table-striped">
                    <thead>
                        <tr>
                            <th scope="col">更改人員</th>
                            <th scope="col">修改時間</th>
                            <th scope="col">子訂單</th>
                            <th scope="col">品名規格</th>
                            <th scope="col">當代推薦人</th>
                            <th scope="col" class="text-end">當代獎金</th>
                            <th scope="col">上代推薦人</th>
                            <th scope="col" class="text-end">上代獎金</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($log as $key => $l)
                            <tr>
                                <td>{{ $l->name }}</td>
                                <td>{{ date('Y/m/d H:i:s', strtotime($l->created_at)) }}</td>
                                <td>{{ $l->sub_order_sn }}</td>
                                <td>{{ $l->product_title }}</td>
                                <td>{{ $l->customer1 }}</td>
                                <td class="text-end">$ {{ number_format($l->bonus1) }}</td>
                                <td>{{ $l->customer2 }}</td>
                                <td class="text-end">$ {{ number_format($l->bonus2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>
        <a href="{{ Route('cms.order.detail', ['id' => $id]) }}" class="btn btn-outline-primary px-4"
            role="button">返回明細</a>
    </div>
@endsection

@once
    @push('sub-styles')
        <style>
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            // 分頁
            $('.nav-link').off('click').on('click', function() {
                const $this = $(this);
                let page = $this.hasClass('-page1') ? 'page1' : '';
                page = $this.hasClass('-page2') ? 'page2' : page;

                // tab
                $('.nav-link').removeClass('active').removeAttr('aria-current');
                $this.addClass('active').attr('aria-current', 'page');
                // page
                $('#page1, #page2').prop('hidden', true);
                $(`#${page}`).prop('hidden', false);
            });

            // 編輯
            $('button.-edit').off('click.edit').on('click.edit', function() {
                $('form').trigger('reset');
                const $this = $(this);

                // btn
                $('button.-edit').prop({
                    hidden: false,
                    disabled: false
                });
                $('button.-save').prop({
                    hidden: true,
                    disabled: true
                });
                $this.prop({
                    hidden: true,
                    disabled: true
                });
                $this.closest('table').find('button.-save').prop({
                    hidden: false,
                    disabled: false
                });

                // input
                $('input[name="bonus1"]').prop({
                    disabled: true,
                    required: false
                });
                $this.closest('table').find('input[name="bonus1"]').prop({
                    disabled: false,
                    required: true
                });
            });

            // 當代獎金 bonus1
            $('input[name="bonus1"]').off('change.edit').on('change.edit', function() {
                const $this = $(this);
                const $bonus2 = $this.closest('table').find('input[name="bonus2"]');

                if ($bonus2.length) {
                    const total_bonus = Number($this.attr('max'));
                    const bonus1 = Number($this.val());
                    $bonus2.val(total_bonus - bonus1);
                }
            });

            // 儲存
            $('button.-save').off('click.save').on('click.save', function() {
                const $this = $(this);
                const _URL = @json(route('api.cms.order.update-profit'));

                const $bonus1 = $this.closest('table').find('input[name="bonus1"]');
                const total_bonus = Number($bonus1.attr('max'));
                const DATA = {
                    profit_id: $this.siblings('input[name="profit_id"]').val(),
                    bonus1: $bonus1.val(),
                    bonus2: $this.closest('table').find('input[name="bonus2"]').val() ?
                        total_bonus - Number($bonus1.val()) : 0
                };

                axios.post(_URL, DATA)
                    .then((result) => {
                        console.log(result.data);
                        if (result.data.status === '0') {
                            toast.show('修改成功');
                            location.reload();
                        } else {
                            toast.show('修改失敗', {
                                type: 'danger'
                            });
                        }
                    }).catch((err) => {
                        toast.show(`發生錯誤：${result.data.message}`, {
                            type: 'danger'
                        });
                    });
            });
        </script>
    @endpush
@endonce
