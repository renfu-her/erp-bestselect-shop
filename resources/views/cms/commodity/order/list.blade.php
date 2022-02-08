@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">訂單管理</h2>
    
    <form id="search" action="" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">訂單關鍵字</label>
                    <input class="form-control" type="text" name="" placeholder="請輸入訂單編號、出貨單號、訂收件人姓名/電話/地址、商品關鍵字">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">商品負責人</label>
                    <input class="form-control" type="text" name="" placeholder="請輸入商品負責人">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">業務員姓名</label>
                    <select name="" class="-select2 -single form-select" data-placeholder="請單選">
                        <option value="" selected disabled>請選擇</option>
                        <option value="1">item 1</option>
                        <option value="2">item 2</option>
                        <option value="3">item 3</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
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
                        <input type="date" class="form-control -endDate @error('_edate') is-invalid @enderror"
                               name="_edate" value="" aria-label="訂購結束日期" />
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
                        <input type="date" class="form-control -endDate @error('_edate') is-invalid @enderror"
                               name="_edate" value="" aria-label="出貨結束日期" />
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
                    <label class="form-label" for="status_code">物態
                        {{-- <i class="bi bi-question-circle" data-bs-toggle="modal" data-bs-target="#status_info"></i> --}}
                    </label>
                    <div class="input-group mb-1">
                        <select class="form-select" id="status_code" aria-label="物態">
                            <option value="" selected>請選擇</option>
                            {{-- @foreach ($status_codes as $code) --}}
                                <option value="a01" class="text-success">待配送</option>
                                <option value="a02" class="text-success">配送中</option>
                                <option value="a03" class="text-success">已送達</option>
                                <option value="c00" class="text-danger">未送達</option>
                            {{-- @endforeach --}}
                        </select>
                        <button class="btn btn-outline-secondary" type="button" id="clear_status_code"
                            data-bs-toggle="tooltip" title="清空">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <input type="hidden" name="selected_code" />
                    <div id="chip-group-status" class="d-flex flex-wrap bd-highlight chipGroup"></div>

                    <!-- Modal -->
                    {{-- <x-b-modal-status id="status_info"></x-b-modal-status> --}}
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label" for="sale_channel">銷售通路</label>
                    <div class="input-group mb-1">
                        <select class="form-select" id="sale_channel" aria-label="銷售通路">
                            <option value="" selected>請選擇</option>
                            {{-- @foreach ($ as $code) --}}
                                <option value="1">官網</option>
                                <option value="2">內網</option>
                                <option value="3">郵政</option>
                                <option value="4">蝦皮</option>
                            {{-- @endforeach --}}
                        </select>
                        <button class="btn btn-outline-secondary" type="button" id="clear_sale_channel"
                            data-bs-toggle="tooltip" title="清空">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <input type="hidden" name="selected_channel" />
                    <div id="chip-group-sale" class="d-flex flex-wrap bd-highlight chipGroup"></div>
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
            {{-- <div class="col">
                <a href="" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> 新增訂單
                </a>
            </div> --}}
            <div class="col-auto">
                顯示
                <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                    @foreach (config('global.dataPerPage') as $value)
                        <option value="{{ $value }}" @if ($data_per_page == $value) selected @endif>{{ $value }}</option>
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
                    {{-- @foreach ($dataList as $key => $data) --}}
                        <tr>
                            <td>2112010000</td>
                            <td>2112010000-1</td>
                            <td>2021/11/09</td>
                            <td>施欽元</td>
                            <td>官網</td>
                            <td class="text-success">待配送</td>
                            <td>
                                <span>46456456</span>
                                <span>77987979</span>
                            </td>
                            <td>自取</td>
                            <td>宅配</td>
                            <td>36354</td>
                            <td>-</td>
                            <td class="text-center">
                                <a href="{{ Route('cms.order.edit', ['id' => 1]) }}"
                                    data-bs-toggle="tooltip" title="明細"
                                    class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-card-list"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>2112010000</td>
                            <td>2112010000-2</td>
                            <td>2021/11/09</td>
                            <td>施欽元</td>
                            <td>官網</td>
                            <td class="text-success">待配送</td>
                            <td>
                                <span>4545646</span>
                            </td>
                            <td>宅配</td>
                            <td>宅配</td>
                            <td>33423</td>
                            <td>-</td>
                            <td class="text-center">
                                <a href="{{ Route('cms.order.edit', ['id' => 1]) }}"
                                    data-bs-toggle="tooltip" title="明細"
                                    class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-card-list"></i>
                                </a>
                            </td>
                        </tr>
                    {{-- @endforeach --}}
                </tbody>
            </table>
        </div>
    </div>
    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            {{-- 頁碼 --}}
            {{-- <div class="d-flex justify-content-center">{{ $dataList->links() }}</div> --}}
        </div>
    </div>
@endsection
@once
    @push('sub-styles')
    <style>
        td > span {
            display: block;
            line-height: 1.3;
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

            // Chip
            // - 物態
            let statusCode = [{"id":2,"title":"待配送","content":"列印託運單","style":"text-success","code":"a01"},
                {"id":3,"title":"配送中","content":"收貨時掃描託運單","style":"text-success","code":"a02"},
                {"id":4,"title":"已送達","content":"拍照上傳簽收單回條","style":"text-success","code":"a03"},
                {"id":5,"title":"未送達","content":"聯繫不上客人暫回喜鴻","style":"text-danger","code":"c00"}
            ];
            let selectedCode = ["a01", "a02", "a03", "c00"];
            let Chips_status = new ChipElem($('#chip-group-status'));
            Chips_status.onDelete = function(code) {
                selectedCode.splice(selectedCode.indexOf(code), 1);
            };
            // - 銷售通路
            let saleChannel = [{"id":1,"title":"官網"},
                {"id":2,"title":"內網"},
                {"id":3,"title":"郵政"},
                {"id":4,"title":"蝦皮"},
            ];
            let selectedChannel = [1, 2, 3, 4];
            let Chips_sale = new ChipElem($('#chip-group-sale'));
            Chips_sale.onDelete = function(id) {
                selectedChannel.splice(selectedChannel.indexOf(id), 1);
            };

            // 初始化
            chipInit();
            function chipInit() {
                // - 物態
                selectedCode.map(function(code) {
                    return statusCode[statusCode.map((v) => v.code).indexOf(code)];
                }).forEach(function(code) {
                    Chips_status.add(code.code, code.title);
                });
                // - 銷售通路
                selectedChannel.map(function(id) {
                    return saleChannel[saleChannel.map((v) => v.id).indexOf(+id)];
                }).forEach(function(channel) {
                    Chips_sale.add(channel.id, channel.title);
                });
            }

            // 綁定事件
            $('#status_code, #sale_channel').off('change.chips').on('change.chips', function(e) {
                const id = $(this).attr('id');
                const val = $(this).val();

                switch (id) {
                    case 'status_code':
                        let code = statusCode[statusCode.map((v) => v.code).indexOf(val)] || {};
                        chipChangeEvent(selectedCode, Chips_status, code.code, code.title);
                        break;
                    case 'sale_channel':
                        let channel = saleChannel[saleChannel.map((v) => v.id).indexOf(+val)] || {};
                        chipChangeEvent(selectedChannel, Chips_sale, channel.id, channel.title);
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
                $('input[name=selected_code]').val(selectedCode);
                $('input[name=selected_channel]').val(selectedChannel);
            });
            // 清空
            $('#clear_status_code').on('click', function(e) {
                selectedCode = [];
                Chips_status.clear();
                e.preventDefault();
            });
            $('#clear_sale_channel').on('click', function(e) {
                selectedChannel = [];
                Chips_sale.clear();
                e.preventDefault();
            });
        </script>
    @endpush
@endOnce
