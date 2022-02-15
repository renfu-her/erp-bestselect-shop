@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">訂單編號-{{ $sn }}</h2>

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
                    <dd>2112010000</dd>
                </div>
                <div class="col">
                    <dt>訂購時間</dt>
                    <dd>2021/12/01</dd>
                </div>
                <div class="col-sm-5">
                    <dt>E-mail</dt>
                    <dd>abc.def123@gmail.com</dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>付款方式</dt>
                    <dd>線上刷卡</dd>
                </div>
                <div class="col">
                    <dt>付款狀態</dt>
                    <dd>已完成</dd>
                </div>
                <div class="col-sm-5">
                    <dt>收款單號</dt>
                    <dd>
                        <span>46456456</span>
                        <span>77987979</span>
                    </dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>購買人姓名</dt>
                    <dd>施欽元</dd>
                </div>
                <div class="col">
                    <dt>購買人電話</dt>
                    <dd>0912345678</dd>
                </div>
                <div class="col-sm-5">
                    <dt>購買人地址</dt>
                    <dd>台北市中正區重慶南路一段58號</dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>收件人姓名</dt>
                    <dd>王之谷</dd>
                </div>
                <div class="col">
                    <dt>收件人電話</dt>
                    <dd>0998765432</dd>
                </div>
                <div class="col-sm-5">
                    <dt>收件人地址</dt>
                    <dd>新北市淡水區新市一路三段176號1樓</dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>統編</dt>
                    <dd>-</dd>
                </div>
                <div class="col">
                    <dt>發票類型</dt>
                    <dd>電子發票</dd>
                </div>
                <div class="col-5">
                    <dt>發票號碼</dt>
                    <dd>AU-12345678</dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>推薦業務員</dt>
                    <dd>王小明-08096</dd>
                </div>
                <div class="col">
                    <dt>寄件人</dt>
                    <dd>王小明</dd>
                </div>
                <div class="col-sm-5">
                    <dt>寄件人地址</dt>
                    <dd>台北市松江路148號6樓之2</dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>銷售通路</dt>
                    <dd>官網</dd>
                </div>
                <div class="col-auto" style="width: calc(100%/12*8.5);">
                    <dt>訂單備註</dt>
                    <dd>測試測試測試測試測試測試測試測試測試</dd>
                </div>
            </dl>
        </div>

        {{-- @foreach ($子明細單 as $item) --}}
        {{-- 常溫 .-detail-warning / 冷凍 .-detail-primary / 冷藏 .-detail-success --}}
        <div class="card shadow mb-4 -detail -detail-warning">
            <div class="card-header px-4 py-3 d-flex align-items-center bg-white">
                <strong class="flex-grow-1 mb-0">BEST-宅配990免運</strong>
                <button type="button" class="btn btn-primary -in-header">列印銷貨單</button>
                <button type="button" class="btn btn-primary -in-header">列印出貨單</button>
            </div>
            <div class="card-body px-4">
                <dl class="row mb-0">
                    <div class="col">
                        <dt>溫層</dt>
                        <dd>常溫</dd>
                    </div>
                    <div class="col">
                        <dt>訂單編號</dt>
                        <dd>2112010000</dd>
                    </div>
                    <div class="col">
                        <dt>出貨單號</dt>
                        <dd>2112010000-1</dd>
                    </div>
                    <div class="col">
                        <dt>消費者物流費用</dt>
                        <dd>$100</dd>
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
                            <tr>
                                <td><a href="#" class="-text">【KINYO】2.4GHz無線鍵鼠組</a></td>
                                <td>1232</td>
                                <td>$50</td>
                                <td>2</td>
                                <td>$100</td>
                            </tr>
                            <tr>
                                <td><a href="#" class="-text">【YADOMA】菌立撤 360度撤菌隨手噴 100mL</a></td>
                                <td>1333</td>
                                <td>$100</td>
                                <td>1</td>
                                <td>$100</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-body px-4 py-0 border-bottom">
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
                                <td class="text-danger">- $50</td>
                            </tr>
                            <tr>
                                <td>優惠劵</td>
                                <td>優惠劵序號</td>
                                <td>-</td>
                                <td class="text-danger">- $60</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-header px-4 text-secondary">物流資訊</div>
            <div class="card-body px-4 pb-4">
                <dl class="row">
                    <div class="col">
                        <dt>運費付款單</dt>
                        <dd>937103</dd>
                    </div>
                    <div class="col">
                        <dt>客戶物流方式</dt>
                        <dd>自取</dd>
                    </div>
                    <div class="col">
                        <dt>實際物流</dt>
                        <dd>宅配</dd>
                    </div>
                    <div class="col">
                        <dt>包裹編號</dt>
                        <dd>36354</dd>
                    </div>
                </dl>
                <dl class="row">
                    <div class="col">
                        <dt>物流說明</dt>
                        <dd>不含箱子費用、不含離島地區</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="card shadow mb-4 -detail -detail-primary">
            <div class="card-header px-4 py-3 d-flex align-items-center bg-white">
                <strong class="flex-grow-1 mb-0">GGC-00455-225冷凍宅配</strong>
                <button type="button" class="btn btn-primary -in-header">列印銷貨單</button>
                <button type="button" class="btn btn-primary -in-header">列印出貨單</button>
            </div>
            <div class="card-body px-4">
                <dl class="row mb-0">
                    <div class="col">
                        <dt>溫層</dt>
                        <dd>冷凍</dd>
                    </div>
                    <div class="col">
                        <dt>訂單編號</dt>
                        <dd>2112010000</dd>
                    </div>
                    <div class="col">
                        <dt>出貨單號</dt>
                        <dd>2112010000-2</dd>
                    </div>
                    <div class="col">
                        <dt>消費者物流費用</dt>
                        <dd>$150</dd>
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
                            <tr>
                                <td><a href="#" class="-text">【春一枝】天然水果手作冰棒</a></td>
                                <td>6543</td>
                                <td>$100</td>
                                <td>2</td>
                                <td>$200</td>
                            </tr>
                            <tr>
                                <td><a href="#" class="-text">紐西蘭冰河帝王鮭魚片（冷煙燻）-(200g/盒)</a></td>
                                <td>4561</td>
                                <td>$150</td>
                                <td>1</td>
                                <td>$150</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-body px-4 py-0 border-bottom">
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
                                <td>-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-header px-4 text-secondary">物流資訊</div>
            <div class="card-body px-4 pb-4">
                <dl class="row">
                    <div class="col">
                        <dt>運費付款單</dt>
                        <dd>937104</dd>
                    </div>
                    <div class="col">
                        <dt>客戶物流方式</dt>
                        <dd>宅配</dd>
                    </div>
                    <div class="col">
                        <dt>實際物流</dt>
                        <dd>宅配</dd>
                    </div>
                    <div class="col">
                        <dt>包裹編號</dt>
                        <dd>33423</dd>
                    </div>
                </dl>
                <dl class="row">
                    <div class="col">
                        <dt>物流說明</dt>
                        <dd>不含箱子費用、不含離島地區</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="card shadow mb-4 -detail -detail-success">
            <div class="card-header px-4 py-3 d-flex align-items-center bg-white">
                <strong class="flex-grow-1 mb-0">ACG-06644-555冷藏宅配</strong>
                <button type="button" class="btn btn-primary -in-header">列印銷貨單</button>
                <button type="button" class="btn btn-primary -in-header">列印出貨單</button>
            </div>
            <div class="card-body px-4">
                <dl class="row mb-0">
                    <div class="col">
                        <dt>溫層</dt>
                        <dd>冷藏</dd>
                    </div>
                    <div class="col">
                        <dt>訂單編號</dt>
                        <dd>2112010000</dd>
                    </div>
                    <div class="col">
                        <dt>出貨單號</dt>
                        <dd>2112010000-3</dd>
                    </div>
                    <div class="col">
                        <dt>消費者物流費用</dt>
                        <dd>$150</dd>
                    </div>
                </dl>
            </div>
        </div>
        {{-- @endforeach --}}

        <div class="card shadow p-4 mb-4">
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
                            <td>$550</td>
                            <td class="text-danger">- $110</td>
                            <td>$440</td>
                            <td>$325</td>
                            <td class="fw-bold">$765</td>
                            <td>7</td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-bordered table-sm text-center align-middle d-table d-sm-none">
                    <tbody>
                        <tr>
                            <td class="col-7 table-light">小計</td>
                            <td>$550</td>
                        </tr>
                        <tr>
                            <td class="col-7 table-light">折扣</td>
                            <td class="text-danger">- $110</td>
                        </tr>
                        <tr>
                            <td class="col-7 table-light lh-sm">折扣後 (不含運)</td>
                            <td>$440</td>
                        </tr>
                        <tr>
                            <td class="col-7 table-light">運費</td>
                            <td>$325</td>
                        </tr>
                        <tr>
                            <td class="col-7 table-light">總金額</td>
                            <td class="fw-bold">$765</td>
                        </tr>
                        <tr>
                            <td class="col-7 table-light lh-sm">預計獲得<a href="#" class="-text">紅利積點</a></td>
                            <td>7</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="submitDiv">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">列印整張訂購單</button>
                <a href="{{ Route('cms.order.index') }}" class="btn btn-outline-primary px-4"
                   role="button">返回列表</a>
            </div>
        </div>
    </form>

@endsection
@once
    @push('sub-styles')
    <link rel="stylesheet" href="{{ Asset('dist/css/order.css') }}">
    <style>
        .table.table-bordered:not(.table-sm ) tr:not(.table-light) {
            height: 70px;
        }
    </style>
    @endpush
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endonce

