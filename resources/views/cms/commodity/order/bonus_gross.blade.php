@extends('layouts.main')
@section('sub-content')
<h2 class="mb-3">獎金毛利</h2>

<ul class="nav nav-tabs border-bottom-0">
    <li class="nav-item">
        <button class="nav-link -page1" aria-current="page" type="button">總覽</button>
    </li>
    <li class="nav-item">
        <button class="nav-link -page2 active" aria-current="page" type="button">毛利資訊</button>
    </li>
    <li class="nav-item">
        <button class="nav-link -page3" type="button">修改紀錄</button>
    </li>
</ul>

<div id="page1" hidden>
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

        <h6>訂單總覽</h6>
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle d-sm-table d-none text-nowrap mb-0">
                @if (!$order->allotted_dividend)
                    <caption class="small text-end pb-0">鴻利預計發放時間：
                        @if (isset($order->dividend_active_at)) {{ date('Y/m/d H:i', strtotime($order->dividend_active_at)) }}
                        @else 未入款 @endif
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
                                預計獲得<a href="{{ route('cms.sale_channel.index') }}" class="-text d-block d-xxl-inline">鴻利點數</a>
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
                </tbody>
            </table>
            <table class="table table-bordered table-sm text-center align-middle d-table d-sm-none mb-0">
                @if (!$order->allotted_dividend)
                    <caption class="small text-end pb-0">鴻利預計發放時間：
                        @if (isset($order->dividend_active_at)) {{ date('Y/m/d H:i', strtotime($order->dividend_active_at)) }}
                        @else 未入款 @endif
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
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="page2">
    <div class="card shadow p-4 mb-4">
        @foreach ($subOrders as $subOrder)
        <form action="#" method="post"></form>
            <div class="define-table table-warning text-nowrap">
                <dl class="d-flex mb-0">
                    <dt class="border">子訂單編號</dt>
                    <dd class="border border-start-0">{{ $subOrder->sn }}</dd>
                </dl>
            </div>
            <div class="table-responsive tableOverBox">
                <table class="table tableList table-striped">
                    <thead>
                        <tr>
                            <th scope="col" style="width:40px">#</th>
                            <th scope="col">品名規格</th>
                            <th scope="col" class="text-center">金額</th>
                            <th scope="col" class="text-center">經銷價</th>
                            <th scope="col" class="text-center">商品成本</th>
                            <th scope="col" class="text-center">數量</th>
                            <th scope="col" class="text-center">小計</th>
                            <th scope="col" class="text-center">毛利</th>
                            <th scope="col" class="text-center">出庫數量</th>
                            <th scope="col">倉庫</th>
                            <th scope="col">入庫單號</th>
                            <th scope="col">產品人員</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subOrder->items as $item)
                            <tr>
                                <th scope="row">1</th>
                                <td>{{ $item->product_title }}</td>
                                <td class="text-center">$ {{ number_format($item->price) }}</td>
                                <td class="text-center">$ {{ number_format(0) }}</td>
                                <td class="text-center">$ {{ number_format(0) }}</td>
                                <td class="text-center">{{ number_format($item->qty) }}</td>
                                <td class="text-center">$ {{ number_format($item->total_price) }}</td>
                                <td class="text-center">$ {{ number_format(0) }}</td>
                                <td class="text-center">{{ number_format(0) }}</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td class="py-0" colspan="11">
                                    <table class="table table-bordered table-sm mb-0">
                                        <tbody>
                                            <tr class="border-top-0 table-light">
                                                <td style="width: 15%;font-weight:500;" class="text-center">總獎金</td>
                                                <td style="width: 25%;font-weight:500;" class="text-center">當代獎金</td>
                                                <td style="width: 25%;font-weight:500;" class="text-center">上代獎金</td>
                                                <td style="width: 25%;font-weight:500;" class="text-center">上代推薦人員</td>
                                                <td style="width: 10%" class="text-center">操作</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">$ {{ number_format(0) }}</td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">$</span>
                                                        <input type="text" class="form-control text-center" aria-label="當代獎金"
                                                            name="bonus" value="0" disabled>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">$</span>
                                                        <input type="text" class="form-control text-center" aria-label="上代獎金"
                                                            name="parent_bonus" value="0" disabled>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="form-control form-control-sm text-center -show" readonly>-</span>
                                                    <input class="form-control form-control-sm text-center" type="text" aria-label="上代推薦人員"
                                                        name="mcode" value="" placeholder="請輸入mcode" hidden disabled>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-primary -edit px-4 me-0">修改</button>
                                                    <button type="button" class="btn btn-sm btn-success -save px-4" hidden disabled>儲存</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>
        @endforeach
    </div>

    <form action="" method="post">
        <div class="card shadow p-4 mb-4">
            <div class="d-flex align-items-center mb-4">
                <button type="button" class="btn btn-sm btn-outline-primary -in-header -edit px-4 me-0">修改</button>
                <button type="submit" class="btn btn-sm btn-success -in-header -save px-4" hidden disabled>儲存</button>
            </div>
            
            <div class="define-table table-light text-nowrap">
                <dl class="d-flex flex-column flex-sm-row">
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>訂單編號</dt>
                        <dd>-</dd>
                    </div>
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>品名規格</dt>
                        <dd>-</dd>
                    </div>
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>金額</dt>
                        <dd>$ {{ number_format(0) }}</dd>
                    </div>
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>經銷價</dt>
                        <dd>$ {{ number_format(0) }}</dd>
                    </div>
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>商品成本</dt>
                        <dd>$ {{ number_format(0) }}</dd>
                    </div>
                </dl>
                <dl class="d-flex flex-column flex-sm-row">
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>數量</dt>
                        <dd>{{ number_format(0) }}</dd>
                    </div>
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>小計</dt>
                        <dd>$ {{ number_format(0) }}</dd>
                    </div>
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>毛利</dt>
                        <dd>$ {{ number_format(0) }}</dd>
                    </div>
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>總獎金</dt>
                        <dd>$ {{ number_format(0) }}</dd>
                    </div>
                </dl>
                <dl class="d-flex flex-column flex-sm-row">
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>當代獎金</dt>
                        <dd>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control text-end text-sm-center" aria-label="當代獎金"
                                    name="bonus" value="0" disabled>
                            </div>
                        </dd>
                    </div>
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>上代獎金</dt>
                        <dd>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control text-end text-sm-center" aria-label="上代獎金"
                                    name="parent_bonus" value="0" disabled>
                            </div>
                        </dd>
                    </div>
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>上代推薦人員</dt>
                        <dd>
                            <span class="form-control form-control-sm -show" readonly>-</span>
                            <input class="form-control form-control-sm text-end text-sm-center" type="text" aria-label="上代推薦人員"
                                name="mcode" value="" placeholder="請輸入mcode" hidden disabled>
                        </dd>
                    </div>
                </dl>
                <dl class="d-flex flex-column flex-sm-row">
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>出庫數量</dt>
                        <dd>{{ number_format(0) }}</dd>
                    </div>
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>倉庫</dt>
                        <dd>-</dd>
                    </div>
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>入庫單號</dt>
                        <dd>-</dd>
                    </div>
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>產品人員</dt>
                        <dd>-</dd>
                    </div>
                </dl>
            </div>
        </div>
    </form>
</div>

<div id="page3" hidden>
    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table tableList table-hover table-striped">
                <thead>
                    <tr>
                        <th scope="col">更改人員</th>
                        <th scope="col">修改時間</th>
                        <th scope="col" class="text-end">當代獎金</th>
                        <th scope="col" class="text-end">上代獎金</th>
                        <th scope="col">上代推薦人員</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Hans</td>
                        <td>{{ date('Y/m/d H:i:s', strtotime('2022/7/4 15:45:55')) }}</td>
                        <td class="text-end">$ {{ number_format(0) }}</td>
                        <td class="text-end">$ {{ number_format(0) }}</td>
                        <td>Hans2.0</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div>
    <a href="{{ Route('cms.order.detail', ['id' => $id]) }}" 
        class="btn btn-outline-primary px-4" role="button">返回明細</a>
</div>
@endsection

@once
    @push('sub-styles')
        <style>
        </style>
    @endpush
    @push('sub-scripts')
    <script>
        $('.nav-link').off('click').on('click', function () {
            const $this = $(this);
            let page = $this.hasClass('-page1') ? 'page1' : '';
            page = $this.hasClass('-page2') ? 'page2' : page;
            page = $this.hasClass('-page3') ? 'page3' : page;

            // tab
            $('.nav-link').removeClass('active').removeAttr('aria-current');
            $this.addClass('active').attr('aria-current', 'page');
            // page
            $('#page1, #page2, #page3').prop('hidden', true);
            $(`#${page}`).prop('hidden', false);
        });

        // 編輯
        $('button.-edit').off('click.edit').on('click.edit', function () {
            $(this).prop({
                hidden: true,
                disabled: true
            });
            $(`button.-save, input[name="mcode"],
                input[name="bonus"], input[name="parent_bonus"]`)
            .prop({
                hidden: false,
                disabled: false
            });
            $('span.-show').prop('hidden', true);
        });

        // test - 儲存
        $('button.-save').off('click.save').on('click.save', function () {
            $(this).prop({
                hidden: true,
                disabled: true
            });
            $('button.-edit').prop({
                hidden: false,
                disabled: false
            });
            $('input[name="bonus"], input[name="parent_bonus"], input[name="mcode"]').prop({
                disabled: true
            });
            $('input[name="mcode"]').prop('hidden', true);
            $('span.-show').prop('hidden', false);
        });

    </script>
    @endpush
@endonce