@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $sn }} 訂單明細</h2>
    @php
        $receivedId = false;
        $route = $receivedId ? 'show' : 'create';
    @endphp

    @if(!$receivable)
        <a href="{{ Route('cms.ar.' . $route, ['id'=>$order->id]) }}" class="btn btn-danger" role="button">{{ !$receivedId ? '新增' : '' }}收款單（暫放）</a>
    @endif

    @php
        include (app_path() . '/Helpers/auth_mpi_mac.php');

        $str_mer_id = '77725';
        $str_merchant_id = '8220300000043';
        $str_terminal_id = '90300043';

        $str_url = 'https://testepos.ctbcbank.com/mauth/SSLAuthUI.jsp';

        $arr_data = [
            'MerchantID'=>$str_merchant_id,
            'TerminalID'=>$str_terminal_id,
            'lidm'=>$order->sn,
            'purchAmt'=>$order->total_price,
            'txType'=>'0',
            'Option'=>0,
            'Key'=>'LPCvSznVxZ4CFjnWbtg4mUWo',
            'MerchantName'=>mb_convert_encoding($order->sale_title, 'BIG5', ['BIG5', 'UTF-8']),
            'AuthResURL'=>route('api.web.order.credit_card_checkout'),
            'OrderDetail'=>mb_convert_encoding($order->note, 'BIG5', ['BIG5', 'UTF-8']),
            'AutoCap'=>'1',
            'Customize'=>' ',
            'debug'=>'0'
        ];

        $str_mac_string = auth_in_mac($arr_data['MerchantID'], $arr_data['TerminalID'], $arr_data['lidm'], $arr_data['purchAmt'], $arr_data['txType'], $arr_data['Option'], $arr_data['Key'], $arr_data['MerchantName'], $arr_data['AuthResURL'], $arr_data['OrderDetail'], $arr_data['AutoCap'], $arr_data['Customize'], $arr_data['debug']);

        $str_url_enc = get_auth_urlenc($arr_data['MerchantID'], $arr_data['TerminalID'], $arr_data['lidm'], $arr_data['purchAmt'], $arr_data['txType'], $arr_data['Option'], $arr_data['Key'], $arr_data['MerchantName'], $arr_data['AuthResURL'], $arr_data['OrderDetail'], $arr_data['AutoCap'], $arr_data['Customize'], $str_mac_string, $arr_data['debug']);
    @endphp
    <form action="{{$str_url}}" method="POST" style="display: inline-block;">
        <input type="hidden" name="MACString" value="{{ $str_mac_string }}">
        <input type="hidden" name="merID" value="{{ $str_mer_id }}">
        <input type="hidden" name="URLEnc" value="{{ $str_url_enc }}">
        <button type="submit" class="btn btn-primary">線上刷卡連結</button>
    </form>

    <form id="form1" method="post" action="">
        @method('POST')
        @csrf

        @error('id')
            <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror

        <div class="card shadow p-4 mb-4">
            <h6>訂單明細</h6>
            <dl class="row">
                <div class="col">
                    <dt>訂單編號</dt>
                    <dd>{{ $order->sn }}</dd>
                </div>
                <div class="col">
                    <dt>訂購時間</dt>
                    <dd>{{ $order->created_at }}</dd>
                </div>
                <div class="col-sm-5">
                    <dt>E-mail</dt>
                    <dd>{{ $order->email }}</dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>付款方式</dt>
                    <dd>(待處理)</dd>
                </div>
                <div class="col">
                    <dt>訂單狀態</dt>
                    <dd>{{ $order->status }}</dd>
                </div>
                <div class="col-sm-5">
                    <dt>收款單號</dt>
                    <dd>
                        @if($receivable)
                        <a href="{{ route('cms.ar.receipt', ['id'=>$order->id]) }}" class="-text">{{ $received_order_data ? $received_order_data->sn : '' }}</a>
                        @else
                        <span>尚未完成收款</span>
                        @endif
                    </dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>購買人姓名</dt>
                    <dd>{{ $order->ord_name }}</dd>
                </div>
                <div class="col">
                    <dt>購買人電話</dt>
                    <dd>{{ $order->ord_phone }}</dd>
                </div>
                <div class="col-sm-5">
                    <dt>購買人地址</dt>
                    <dd>{{ $order->ord_address }}</dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>收件人姓名</dt>
                    <dd>{{ $order->rec_name }}</dd>
                </div>
                <div class="col">
                    <dt>收件人電話</dt>
                    <dd>{{ $order->rec_phone }}</dd>
                </div>
                <div class="col-sm-5">
                    <dt>收件人地址</dt>
                    <dd>{{ $order->ord_address }}</dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>統編</dt>
                    <dd>(待處理)</dd>
                </div>
                <div class="col">
                    <dt>發票類型</dt>
                    <dd>(待處理)</dd>
                </div>
                <div class="col-5">
                    <dt>發票號碼</dt>
                    <dd>(待處理)</dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>推薦業務員</dt>
                    <dd>(待處理)</dd>
                </div>
                <div class="col">
                    <dt>寄件人</dt>
                    <dd>{{ $order->sed_name }}</dd>
                </div>
                <div class="col-sm-5">
                    <dt>寄件人地址</dt>
                    <dd>{{ $order->sed_address }}</dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>銷售通路</dt>
                    <dd>{{ $order->sale_title }}</dd>
                </div>
                <div class="col-auto" style="width: calc(100%/12*8.5);">
                    <dt>訂單備註</dt>
                    <dd>{{ $order->note }}</dd>
                </div>
            </dl>
        </div>
        @php
            $dlv_fee = 0;
            $price = 0;
            
        @endphp
        @foreach ($subOrders as $subOrder)
            @php
                $dlv_fee += $subOrder->dlv_fee;
                $price += $subOrder->total_price;
            @endphp
            {{-- 宅配 .-detail-primary / 自取 .-detail-warning / 超取 .-detail-success --}}
            @if (true == isset($subOrderId) && $subOrder->id != $subOrderId)
                @continue
            @endif
            <div @class([
                'card shadow mb-4 -detail',
                '-detail-primary' => $subOrder->ship_category === 'deliver',
                '-detail-warning' => $subOrder->ship_category === 'pickup',
            ])>
                <div class="card-header px-4 d-flex align-items-center bg-white flex-wrap justify-content-end">
                    <strong class="flex-grow-1 mb-0">{{ $subOrder->ship_event }}</strong>
                    <span class="badge -badge fs-6">{{ $subOrder->ship_category_name }}</span>
                    @if(true == isset($subOrderId))
                    <div class="col-12 d-flex justify-content-end mt-2">
                        <a class="btn btn-sm btn-success -in-header" href="{{ Route('cms.logistic.changeLogisticStatus', ['event' => \App\Enums\Delivery\Event::order()->value, 'eventId' => $subOrder->id], true) }}">配送狀態</a>
                        <a class="btn btn-sm btn-success -in-header" href="{{ Route('cms.logistic.create', ['event' => \App\Enums\Delivery\Event::order()->value, 'eventId' => $subOrder->id], true) }}">物流設定</a>
                        <a class="btn btn-sm btn-success -in-header" href="{{ Route('cms.delivery.create', ['event' => \App\Enums\Delivery\Event::order()->value, 'eventId' => $subOrder->id], true) }}">出貨審核</a>
                        <button type="button" class="btn btn-sm btn-primary -in-header">列印銷貨單</button>
                        <button type="button" class="btn btn-sm btn-primary -in-header">列印出貨單</button>
                    </div>
                    @endif
                </div>
                <div class="card-body px-4">
                    <dl class="row mb-0">
                        <div class="col">
                            <dt>溫層</dt>
                            <dd>{{ $subOrder->ship_temp ?? '-' }}</dd>
                        </div>
                        <div class="col">
                            <dt>訂單編號</dt>
                            <dd>{{ $subOrder->sn }}</dd>
                        </div>
                        <div class="col">
                            <dt>出貨單號</dt>
                            <dd>{{ $subOrder->delivery_sn ?? '(待處理)' }}</dd>
                        </div>
                        <div class="col">
                            <dt>消費者物流費用</dt>
                            <dd>${{ number_format($subOrder->dlv_fee) }}</dd>
                        </div>
                    </dl>
                </div>
                <div class="card-body px-4 py-0">
                    <div class="table-responsive tableOverBox">
                        <table class="table tableList table-sm mb-0">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th scope="col">商品名稱</th>
                                    <th scope="col">SKU</th>
                                    <th scope="col">單價</th>
                                    <th scope="col">數量</th>
                                    <th scope="col">小計</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subOrder->items as $item)
                                    <tr>
                                        <td><a href="#" class="-text">{{ $item->product_title }}</a></td>
                                        <td>{{ $item->sku }}</td>
                                        <td>${{ number_format($item->price) }}</td>
                                        <td>{{ $item->qty }}</td>
                                        <td>${{ number_format($item->total_price) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- <div class="card-body px-4 py-0" hidden>
                    <div class="table-responsive tableOverBox">
                        <table class="table tableList table-sm mb-0">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th scope="col">優惠類型</th>
                                    <th scope="col">優惠名稱</th>
                                    <th scope="col">贈品</th>
                                    <th scope="col">金額</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>贈品</td>
                                    <td>-</td>
                                    <td>滑鼠墊</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>金額</td>
                                    <td>滿額贈</td>
                                    <td>-</td>
                                    <td class="text-danger">- ${{ number_format(50) }}</td>
                                </tr>
                                <tr>
                                    <td>優惠劵</td>
                                    <td>優惠劵序號</td>
                                    <td>-</td>
                                    <td class="text-danger">- ${{ number_format(60) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div> --}}

                <div class="card-header px-4 text-secondary border-top">物流資訊</div>
                <div class="card-body px-4 pb-4">
                    <dl class="row">
                        <div class="col">
                            <dt>運費付款單</dt>
                            <dd>(待處理)</dd>
                        </div>
                        <div class="col">
                            <dt>客戶物流方式</dt>
                            <dd>{{ $subOrder->ship_event }}</dd>
                        </div>
                        <div class="col">
                            <dt>實際物流</dt>
                            <dd>{{ $subOrder->ship_group_name ?? '(待處理)' }}</dd>
                        </div>
                        <div class="col">
                            <dt>包裹編號</dt>
                            <dd>{{ $subOrder->package_sn ?? '(待處理)' }}</dd>
                        </div>
                    </dl>
                    <dl class="row">
                        <div class="col">
                            <dt>物態</dt>
                            <dd>{{ $subOrder->logistic_status ?? '(待處理)' }}</dd>
                        </div>
                        <div class="col-9">
                            <dt>物流說明</dt>
                            <dd>{{ $subOrder->ship_group_note ?? '(待處理)' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        @endforeach


        @if (false == isset($subOrderId))
            <div class="card shadow p-4 mb-4">
                @if (count($discounts) > 0)
                <h6>折扣明細</h6>
                <div class="table-responsive">
                    <table class="table table-sm text-right align-middle">
                        <tbody>
                            @foreach ($discounts as $key => $dis)
                                <tr>
                                    <td class="col-8">{{ $dis->title }}</td>
                                    @if ($dis->method_code == 'coupon')
                                        <td class="text-end pe-4">{{ $dis->extra_title }}</td>
                                    @elseif (is_numeric($dis->discount_value))
                                        <td class="text-end pe-4 text-danger">- ${{ number_format($dis->discount_value) }}</td>
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
                    <table class="table table-bordered text-center align-middle d-sm-table d-none text-nowrap">
                        <tbody>
                            <tr class="table-light">
                                <td class="col-2">小計</td>
                                <td class="col-2">折扣</td>
                                <td class="col-2 lh-sm">折扣後 <br class="d-xxl-none">(不含運)</td>
                                <td class="col-2">運費</td>
                                <td class="col-2">總金額</td>
                                <td class="col-2 lh-sm">預計獲得<a href="#" class="-text d-block d-xxl-inline">紅利積點</a></td>
                            </tr>
                            <tr>
                                <td>${{ number_format($order->origin_price) }}</td>
                                <td class="text-danger">- ${{ number_format($order->discount_value) }}</td>
                                <td>${{ number_format($order->discounted_price) }}</td>
                                <td>${{ number_format($order->dlv_fee) }}</td>
                                <td class="fw-bold">${{ number_format($order->total_price) }}</td>
                                <td>-</td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="table table-bordered table-sm text-center align-middle d-table d-sm-none">
                        <tbody>
                            <tr>
                                <td class="col-7 table-light">小計</td>
                                <td class="text-end pe-4">${{ number_format($order->origin_price) }}</td>
                            </tr>
                            <tr>
                                <td class="col-7 table-light">折扣 </td>
                                <td class="text-danger text-end pe-4">- ${{ number_format($order->discount_value) }}</td>
                            </tr>
                            <tr>
                                <td class="col-7 table-light lh-sm">折扣後 (不含運)</td>
                                <td class="text-end pe-4">${{ number_format($order->discounted_price) }}</td>
                            </tr>
                            <tr>
                                <td class="col-7 table-light">運費</td>
                                <td class="text-end pe-4">${{ number_format($order->dlv_fee) }}</td>
                            </tr>
                            <tr>
                                <td class="col-7 table-light">總金額</td>
                                <td class="fw-bold text-end pe-4">${{ number_format($order->total_price) }}</td>
                            </tr>
                            <tr>
                                <td class="col-7 table-light lh-sm">預計獲得<a href="#" class="-text">紅利積點</a></td>
                                <td class="text-end pe-4">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div id="submitDiv">
            <div class="col-auto">
                @if (false == isset($subOrderId))
                    <button type="submit" class="btn btn-primary px-4">列印整張訂購單</button>
                    <a href="{{ Route('cms.order.index') }}" class="btn btn-outline-primary px-4" role="button">返回列表</a>
                @else
                    <a href="{{ Route('cms.delivery.index') }}" class="btn btn-outline-primary px-4"
                        role="button">返回列表</a>
                @endif
            </div>
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
    @endpush
@endonce
