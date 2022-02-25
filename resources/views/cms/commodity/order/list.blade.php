@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">訂單管理</h2>

    <form id="search" action="" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 col-xxl-3 mb-3">
                    <label class="form-label">訂單關鍵字</label>
                    <input class="form-control" type="text" value="{{ $cond['keyword'] }}" name="keyword"
                        placeholder="請輸入訂單編號">
                </div>
                {{-- <div class="col-12 col-sm-6 col-xxl-3 mb-3">
                    <label class="form-label">商品負責人</label>
                    <input class="form-control" type="text" name="" placeholder="請輸入商品負責人">
                </div>
                <div class="col-12 col-sm-6 col-xxl-3 mb-3">
                    <label class="form-label">業務員姓名</label>
                    <select name="" class="-select2 -single form-select" data-placeholder="請單選">
                        <option value="" selected disabled>請選擇</option>
                        <option value="1">item 1</option>
                        <option value="2">item 2</option>
                        <option value="3">item 3</option>
                    </select>
                </div> --}}
                <div class="col-12 col-sm-6 col-xxl-3 mb-3">
                    <label class="form-label">分潤人姓名</label>
                    <select name="" class="form-select">
                        <option value="" selected>請選擇採購廠商</option>
                        <option value="1">item 1</option>
                        <option value="2">item 2</option>
                        <option value="3">item 3</option>
                    </select>
                </div>
                <div class="col-12 col-xxl-6 mb-3">
                    <label class="form-label">訂購日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('_sdate') is-invalid @enderror"
                            name="_sdate" value="" aria-label="訂購起始日期" />
                        <input type="date" class="form-control -endDate @error('_edate') is-invalid @enderror" name="_edate"
                            value="" aria-label="訂購結束日期" />
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('_sdate')
                                {{ $message }}
                            @enderror
                            @error('_edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xxl-6 mb-3">
                    <label class="form-label">出貨日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('_sdate') is-invalid @enderror"
                            name="_sdate" value="" aria-label="出貨起始日期" />
                        <input type="date" class="form-control -endDate @error('_edate') is-invalid @enderror" name="_edate"
                            value="" aria-label="出貨結束日期" />
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('_sdate')
                                {{ $message }}
                            @enderror
                            @error('_edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label" for="shipment_status">物態
                        {{-- <i class="bi bi-question-circle" data-bs-toggle="modal" data-bs-target="#status_info"></i> --}}
                    </label>
                    <div class="input-group mb-1">
                        <select class="form-select" id="shipment_status" aria-label="物態">
                            <option value="" selected>請選擇</option>
                            {{-- @foreach ($shipment_statuss as $code) --}}
                            <option value="a01" class="text-success">待配送</option>
                            <option value="a02" class="text-success">配送中</option>
                            <option value="a03" class="text-success">已送達</option>
                            <option value="c00" class="text-danger">未送達</option>
                            {{-- @endforeach --}}
                        </select>
                        <button class="btn btn-outline-secondary" type="button" id="clear_shipment_status"
                            data-bs-toggle="tooltip" title="清空">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <input type="hidden" name="shipment_status" />
                    <div id="chip-group-shipment" class="d-flex flex-wrap bd-highlight chipGroup"></div>

                    <!-- Modal 說明 -->
                    {{-- <x-b-modal-status id="status_info"></x-b-modal-status> --}}
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label" for="order_status">訂單狀態</label>
                    <div class="input-group mb-1">
                        <select class="form-select" id="order_status" aria-label="訂單狀態">
                            <option value="" selected>請選擇</option>
                            @foreach ($orderStatus as $key => $oStatus)
                                <option value="{{ $oStatus->id }}">{{ $oStatus->title }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-outline-secondary" type="button" id="clear_order_status"
                            data-bs-toggle="tooltip" title="清空">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <input type="hidden" name="order_status" />
                    <div id="chip-group-order" class="d-flex flex-wrap bd-highlight chipGroup"></div>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">銷售通路</label>
                    <select name="sale_channel_id" id="select2-multiple" multiple class="-select2 -multiple form-select"
                        data-placeholder="可多選">
                        @foreach ($saleChannels as $sale)
                            <option value="{{ $sale['id'] }}">{{ $sale['title'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $data_per_page }}" />
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>

    <div class="card shadow p-4 mb-4">
        <div class="row justify-content-end mb-4">
            <div class="col">
                <a href="{{ Route('cms.order.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> 新增訂單
                </a>
            </div>
            <div class="col-auto">
                顯示
                <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                    @foreach (config('global.dataPerPage') as $value)
                        <option value="{{ $value }}" @if ($data_per_page == $value) selected @endif>
                            {{ $value }}</option>
                    @endforeach
                </select>
                筆
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col">訂單編號</th>
                        <th scope="col">出貨單號</th>
                        <th scope="col">訂購日期</th>
                        <th scope="col">購買人</th>
                        <th scope="col">銷售通路</th>
                        <th scope="col">物態</th>
                        <th scope="col">收款單號</th>
                        <th scope="col">客戶物流方式</th>
                        <th scope="col">實際物流</th>
                        <th scope="col">包裹編號</th>
                        <th scope="col">退貨狀態</th>
                        <th scope="col" class="text-center">明細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <td>{{ $data->order_sn }}</td>
                            <td></td>
                            <td>{{ $data->order_date }}</td>
                            <td>{{ $data->name }}</td>
                            <td>{{ $data->sale_title }}</td>
                            <td class="text-success">待配送</td>
                            <td>
                                <span class="d-block lh-sm">46456456</span>
                                <span class="d-block lh-sm">77987979</span>
                            </td>
                            <td>{{ $data->ship_category_name }}</td>
                            <td>{{ $data->ship_event }}</td>
                            <td>{{ $data->ship_sn }}</td>
                            <td>-</td>
                            <td class="text-center">
                                <a href="{{ Route('cms.order.detail', ['id' => $data->id]) }}" data-bs-toggle="tooltip"
                                    title="明細" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-card-list"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            {{-- 頁碼 --}}
            <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
        </div>
    </div>
@endsection
@once
    @push('sub-scripts')
        <script>
            // 顯示筆數
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });

            // Chip
            // - 物態
            let shipmentStatus = [{
                    "id": 2,
                    "title": "待配送",
                    "content": "列印託運單",
                    "style": "text-success",
                    "code": "a01"
                },
                {
                    "id": 3,
                    "title": "配送中",
                    "content": "收貨時掃描託運單",
                    "style": "text-success",
                    "code": "a02"
                },
                {
                    "id": 4,
                    "title": "已送達",
                    "content": "拍照上傳簽收單回條",
                    "style": "text-success",
                    "code": "a03"
                },
                {
                    "id": 5,
                    "title": "未送達",
                    "content": "聯繫不上客人暫回喜鴻",
                    "style": "text-danger",
                    "code": "c00"
                }
            ];
            let selectedShipment = ["a01", "a02", "a03", "c00"];
            let Chips_shipment = new ChipElem($('#chip-group-shipment'));
            Chips_shipment.onDelete = function(code) {
                selectedShipment.splice(selectedShipment.indexOf(code), 1);
            };
            // - 訂單狀態
            let orderStatus = @json($orderStatus);

            let selectedOrder = @json($cond['order_status']);
            let Chips_order = new ChipElem($('#chip-group-order'));
            Chips_order.onDelete = function(id) {
                selectedOrder.splice(selectedOrder.indexOf(id), 1);
            };

            // 初始化
            chipInit();

            function chipInit() {
                // - 物態
                selectedShipment.map(function(code) {
                    return shipmentStatus[shipmentStatus.map((v) => v.code).indexOf(code)];
                }).forEach(function(code) {
                    Chips_shipment.add(code.code, code.title);
                });
                // - 訂單狀態
                selectedOrder.map(function(id) {
                    return orderStatus[orderStatus.map((v) => v.id).indexOf(id)];
                }).forEach(function(status) {
                    Chips_order.add(status.id, status.title);
                });
            }

            // 綁定事件
            $('#shipment_status, #order_status').off('change.chips').on('change.chips', function(e) {
                const id = $(this).attr('id');
                const val = $(this).val();

                switch (id) {
                    case 'shipment_status':
                        let code = shipmentStatus[shipmentStatus.map((v) => v.code).indexOf(val)] || {};
                        chipChangeEvent(selectedShipment, Chips_shipment, code.code, code.title);
                        break;
                    case 'order_status':
                        let channel = orderStatus[orderStatus.map((v) => v.id).indexOf(val)] || {};
                        chipChangeEvent(selectedOrder, Chips_order, channel.id, channel.title);
                        break;
                }

                $(this).val('');

                function chipChangeEvent(thisSelected, thisChips, index, title) {
                    if (thisSelected.indexOf(index) === -1) {
                        thisSelected.push(index);
                        thisChips.add(index, title);
                    }
                }
            });
            // 送出前存值
            $('#search').on('submit', function(e) {
                $('input[name="shipment_status"]').val(selectedShipment);
                $('input[name=order_status]').val(selectedOrder);
            });
            // 清空
            $('#clear_shipment_status').on('click', function(e) {
                selectedShipment = [];
                Chips_shipment.clear();
                e.preventDefault();
            });
            $('#clear_order_status').on('click', function(e) {
                selectedOrder = [];
                Chips_order.clear();
                e.preventDefault();
            });
        </script>
    @endpush
@endOnce
