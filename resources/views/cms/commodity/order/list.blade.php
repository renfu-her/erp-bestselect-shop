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
                        placeholder="請輸入訂單編號、姓名、電話">
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
                @if($canViewWholeOrder)
                <div class="col-12 col-sm-6 col-xxl-3 mb-3">
                    <label class="form-label">分潤人姓名</label>
                    <select name="profit_user" class="form-select -select2 -single">
                        <option value="" selected>請選擇</option>
                        @foreach ($profitUsers as $key => $user)
                            <option value="{{ $user->customer_id }}" @if ($cond['profit_user'] == $user->customer_id) selected @endif>
                                {{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-12 col-xxl-6 mb-3">
                    <label class="form-label">訂購日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('order_sdate') is-invalid @enderror"
                            name="order_sdate" value="{{ $cond['order_sdate'] }}" aria-label="訂購起始日期" />
                        <input type="date" class="form-control -endDate @error('order_edate') is-invalid @enderror"
                            name="order_edate" value="{{ $cond['order_edate'] }}" aria-label="訂購結束日期" />
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
                        <input type="date" class="form-control -startDate @error('dlv_sdate') is-invalid @enderror"
                            name="dlv_sdate" value="" aria-label="出貨起始日期" />
                        <input type="date" class="form-control -endDate @error('dlv_edate') is-invalid @enderror"
                            name="dlv_edate" value="" aria-label="出貨結束日期" />
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('dlv_sdate')
                                {{ $message }}
                            @enderror
                            @error('dlv_edate')
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
                        <select class="form-select" id="shipment_status" name="shipment_status" aria-label="物態">
                            <option value="" selected>請選擇</option>
                            @foreach ($shipmentStatus as $key => $sStatus)
                                <option value="{{ $key }}">{{ $sStatus }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-outline-secondary" type="button" id="clear_shipment_status"
                            data-bs-toggle="tooltip" title="清空">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <input type="hidden" name="shipment_status" value="{{ implode(',', $cond['shipment_status']) }}" />
                    <div id="chip-group-shipment" class="d-flex flex-wrap bd-highlight chipGroup"></div>

                    <!-- Modal 說明 -->
                    {{-- <x-b-modal-status id="status_info"></x-b-modal-status> --}}
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">訂單狀態</label>
                    <select name="order_status[]" multiple class="-select2 -multiple form-select"
                        data-placeholder="請選擇訂單狀態">
                        @foreach ($orderStatus as $key => $oStatus)
                            <option value="{{ $key }}" @if (in_array($key, $cond['order_status'])) selected @endif>
                                {{ $oStatus }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">付款方式</label>
                    <select name="received_method[]" multiple class="-select2 -multiple form-select"
                            data-placeholder="請選擇付款方式">
                        @foreach ($receivedMethods as $key => $receivedMethod)
                            <option value="{{ $key }}" @if (in_array($key, $cond['received_method'])) selected @endif>
                                {{ $receivedMethod }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">銷售通路</label>
                    <select name="sale_channel_id[]" multiple class="-select2 -multiple form-select"
                        data-placeholder="請選擇銷售通路">
                        @foreach ($saleChannels as $sale)
                            <option value="{{ $sale['id'] }}" @if (in_array($sale['id'], $cond['sale_channel_id'])) selected @endif>
                                {{ $sale['title'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-xxl-3 mb-3">
                    <label class="form-label">商品名稱</label>
                    <input class="form-control" type="text" value="{{ $cond['item_title'] }}" name="item_title"
                           placeholder="請輸入商品名稱">
                </div>
                <div class="col-12 col-sm-6 col-xxl-3 mb-3">
                    <label class="form-label">採購單號</label>
                    <input class="form-control" type="text" value="{{ $cond['purchase_sn'] }}" name="purchase_sn"
                           placeholder="請輸入採購單號">
                </div>
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">銷貨退回單</legend>
                    <div class="px-1 pt-1">
                        @foreach ($has_back_sn as $key => $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="has_back_sn" type="radio"
                                           value="{{ $value[0] }}" @if ($value[0] == $cond['has_back_sn']) checked @endif>
                                    {{ $value[1] }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
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
                <div class="btn-group">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                        data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        顯示欄位
                    </button>
                    <ul id="selectField" class="dropdown-menu">
                    </ul>
                </div>
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

        <div class="table-responsive tableOverBox mb-3">
            <table class="table table-striped tableList small mb-0">
                <thead class="align-middle">
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <th scope="col" style="width:40px" class="text-center">明細</th>
                        <th scope="col">訂單編號</th>
                        <th scope="col">費用</th>
                        <th scope="col">訂單狀態</th>
                        <th scope="col">物流狀態</th>
                        <th scope="col">付款方式</th>
                        <th scope="col">出貨單號</th>
                        <th scope="col">訂購日期</th>
                        <th scope="col">購買人</th>
                        <th scope="col">銷售通路</th>
                        <th scope="col">收款單號</th>
                        <th scope="col">客戶物流</th>
                        <th scope="col">實際物流</th>
                        <th scope="col">出貨日期</th>
                        <th scope="col">包裹編號</th>
                        <th scope="col">產品名稱</th>
                        <th scope="col">數量</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row" class="fs-6">{{ $key + 1 }}</th>
                            <td class="text-center fs-6">
                                @can('cms.order.detail')
                                    <a href="{{ Route('cms.order.detail', ['id' => $data->id]) }}" data-bs-toggle="tooltip"
                                        title="明細" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-card-list"></i>
                                    </a>
                                @endcan
                            </td>
                            <td class="wrap">{{ $data->order_sn }}</td>
                            <td>
                                ${{ number_format($data->total_price) }}
                            </td>
                            <td @class(['fs-6', 'text-danger' => $data->order_status === '取消'])>
                                {{ $data->order_status }}
                            </td>
                            <td class="fs-6">{{ $data->logistic_status }}</td>
                            <td>{{ $data->payment_method_title }}</td>
                            <td>
                                @if ($data->projlgt_order_sn)
                                    <a href="{{ env('LOGISTIC_URL') . 'guest/order-flow/' . $data->projlgt_order_sn }}"
                                        target="_blank">
                                        {{ $data->projlgt_order_sn }}
                                    </a>
                                @else
                                    {{ $data->package_sn }}
                                @endif
                            </td>
                            <td>{{ date('Y/m/d', strtotime($data->order_date)) }}</td>
                            <td class="wrap">{{ $data->name }}</td>
                            <td>{{ $data->sale_title }}</td>
                            <td>{{ $data->or_sn }}</td>
                            <td class="wrap">
                                <div class="lh-1 text-nowrap">
                                    <span @class([
                                        'badge -badge',
                                        '-primary' => $data->ship_category_name === '宅配',
                                        '-warning' => $data->ship_category_name === '自取',
                                    ])>{{ $data->ship_category_name }}</span>
                                </div>
                                <div class="lh-base text-nowrap">{{ $data->ship_event }}</div>
                            </td>
                            <td>{{ $data->ship_group_name }}</td>
                            <td>{{ $data->dlv_audit_date }}</td>
                            <td>{{ $data->package_sn }}</td>
                            <td class="py-0 lh-base">
                                <ul class="list-group list-group-flush">
                                @foreach($data->productTitleGroup as $x => $productTitle)
                                    <li class="list-group-item bg-transparent pe-1">{{ $productTitle->product_title }}</li>
                                @endforeach
                                </ul>
                            </td>
                            <td class="py-0 lh-base">
                                <ul class="list-group list-group-flush">
                                    @foreach($data->productTitleGroup as $x => $productTitle)
                                        <li class="list-group-item bg-transparent pe-1">{{ $productTitle->qty }}</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="fs-5 fw-bold text-end">
            合計金額（搜尋結果的費用總和）：${{ number_format($somOfPrice ?? '') }}
            （共 {{ $dataList->total() }} 筆）
        </div>
    </div>
    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            @if ($dataList)
                <div class="mx-3">共 {{ $dataList->lastPage() }} 頁(共找到 {{ $dataList->total() }} 筆資料)</div>
                {{-- 頁碼 --}}
                <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
            @endif
        </div>
    </div>
@endsection
@once
    @push('sub-styles')
        <style>
            .badge.-badge {
                color: #484848;
            }

            .badge.-badge.-primary {
                background-color: #cfe2ff;
            }

            .badge.-badge.-warning {
                background-color: #fff3cd;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            // 顯示筆數
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });

            // 選擇表格顯示欄位
            let DefHide = {};
            try {
                DefHide = JSON.parse(localStorage.getItem('table-hide-field')) || {};
            } catch (error) {}
            const Key = location.pathname;

            setPrintTrCheckbox($('table.tableList'), $('#selectField'),
                { type: 'dropdown', defaultHide: DefHide[Key] || [] }
            );
            // 紀錄選項
            $('#selectField').parent().on('hidden.bs.dropdown', function () {
                let temp = [];
                $('#selectField input[type="checkbox"][data-nth]').each((i, elem) => {
                    if (!$(elem).prop('checked')) {
                        temp.push(Number($(elem).data('nth')));
                    }
                });
                localStorage.setItem('table-hide-field', JSON.stringify({
                    ...DefHide,
                    [Key]: temp
                }));
            });

            // - 物態
            let selectedShipment = $('input[name="shipment_status"]').val();
            const shipmentStatus = @json($shipmentStatus) || [];
            let all_shipmentStatus = {};

            Object.keys(shipmentStatus).forEach((key) => {
                all_shipmentStatus[key] = shipmentStatus[key];
            });

            let Chips_shipment = new ChipElem($('#chip-group-shipment'));

            // 初始化
            selectedShipment = Chips_shipment.init(selectedShipment, all_shipmentStatus);

            // 綁定事件
            Chips_shipment.onDelete = function(code) {
                selectedShipment.splice(selectedShipment.indexOf(code), 1);
            };
            $('#shipment_status, #order_status').off('change.chips').on('change.chips', function(e) {
                const id = $(this).attr('id');
                const val = $(this).val();
                const title = $(this).children(':selected').text();

                switch (id) {
                    case 'shipment_status':
                        chipChangeEvent(selectedShipment, Chips_shipment, val, title);
                        break;
                }

                $(this).val('');

                function chipChangeEvent(thisSelected, thisChips, value, title) {
                    if (thisSelected.indexOf(value) === -1) {
                        thisSelected.push(value);
                        thisChips.add(value, title);
                    }
                }
            });
            // 送出前存值
            $('#search').on('submit', function(e) {
                $('input[name="shipment_status"]').val(selectedShipment);
            });
            // 清空
            $('#clear_shipment_status').on('click', function(e) {
                selectedShipment = [];
                Chips_shipment.clear();
                e.preventDefault();
            });
        </script>
    @endpush
@endOnce
