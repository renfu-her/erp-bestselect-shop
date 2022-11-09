@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">出貨管理</h2>

    <form id="search" action="{{ Route('cms.delivery.index') }}" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">出貨單號</label>
                    <input class="form-control" value="{{ $searchParam['delivery_sn'] }}" type="text" name="delivery_sn"
                        placeholder="輸入出貨單號">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">單據編號</label>
                    <input class="form-control" value="{{ $searchParam['event_sn'] }}" type="text" name="event_sn"
                        placeholder="輸入訂購單、轉倉單號">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">出貨倉</label>
                    <select class="form-select -select2 -multiple" multiple name="receive_depot_id[]" aria-label="由哪一個倉庫出貨"
                        data-placeholder="多選">
                        @foreach ($depotList as $key => $data)
                            <option value="{{ $data->id }}" @if (in_array($data->id, $searchParam['receive_depot_id'] ?? [])) selected @endif>
                                {{ $data->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">物流分類</label>
                    <select class="form-select -select2 -multiple" multiple name="ship_method[]" aria-label="物流分類"
                        data-placeholder="多選">
                        <option value="喜鴻出貨" @if (in_array('喜鴻出貨', $searchParam['ship_method'] ?? [])) ) selected @endif>喜鴻出貨</option>
                        <option value="廠商出貨" @if (in_array('廠商出貨', $searchParam['ship_method'] ?? [])) selected @endif>廠商出貨</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">物態</label>
                    <select class="form-select -select2 -multiple" multiple name="logistic_status_code[]" aria-label="物態"
                        data-placeholder="多選">
                        @foreach ($logisticStatus as $key => $value)
                            <option value="{{ $key }}" @if (in_array($key, $searchParam['logistic_status_code'] ?? [])) selected @endif>
                                {{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">物流型態</label>
                    <select class="form-select -select2 -multiple" multiple name="ship_category[]" aria-label="物流型態"
                        data-placeholder="多選">
                        @foreach ($shipmentCategory as $key => $value)
                            <option value="{{ $value->code }}" @if (in_array($value->code, $searchParam['ship_category'] ?? [])) selected @endif>
                                {{ $value->category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">物流溫層</label>
                    <select class="form-select -select2 -multiple" multiple name="ship_temp_id[]" aria-label="物流溫層"
                            data-placeholder="多選">
                        @foreach ($temps as $key => $value)
                            <option value="{{ $value->id }}" @if (in_array($value->id, $searchParam['ship_temp_id'] ?? [])) selected @endif>
                                {{ $value->temps }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">自取倉溫層</label>
                    <select class="form-select -select2 -multiple" multiple name="depot_temp_id[]" aria-label="自取倉溫層"
                        data-placeholder="多選">
                        @foreach ($temps as $key => $value)
                            <option value="{{ $value->id }}" @if (in_array($value->id, $searchParam['depot_temp_id'] ?? [])) selected @endif>
                                {{ $value->temps }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">訂單狀態</label>
                    <select class="form-select -select2 -multiple" multiple name="order_status[]" aria-label="訂單狀態"
                        data-placeholder="多選">
                        @foreach ($order_status as $key => $value)
                            <option value="{{ $value }}" @if (in_array($value, $searchParam['order_status'] ?? [])) selected @endif>
                                {{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">含寄倉出貨</legend>
                    <div class="px-1 pt-1">
                        @foreach ($has_csn as $key => $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="has_csn" type="radio"
                                        value="{{ $value[0] }}" @if ($value[0] == $searchParam['has_csn']) checked @endif>
                                    {{ $value[1] }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">銷貨退回單</legend>
                    <div class="px-1 pt-1">
                        @foreach ($has_back_sn as $key => $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="has_back_sn" type="radio"
                                           value="{{ $value[0] }}" @if ($value[0] == $searchParam['has_back_sn']) checked @endif>
                                    {{ $value[1] }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>

                <div class="col-12 mb-3">
                    <label class="form-label">訂單起訖日期</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('order_sdate') is-invalid @enderror"
                            name="order_sdate" value="{{ $searchParam['order_sdate'] }}" aria-label="訂單起始日期" />
                        <input type="date" class="form-control -endDate @error('order_edate') is-invalid @enderror"
                            name="order_edate" value="{{ $searchParam['order_edate'] }}" aria-label="訂單結束日期" />
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('order_sdate')
                                {{ $message }}
                            @enderror
                            @error('order_edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">出貨起訖日期</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('delivery_sdate') is-invalid @enderror"
                            name="delivery_sdate" value="{{ $searchParam['delivery_sdate'] }}" aria-label="出貨起始日期" />
                        <input type="date" class="form-control -endDate @error('order_edate') is-invalid @enderror"
                            name="delivery_edate" value="{{ $searchParam['delivery_edate'] }}" aria-label="出貨結束日期" />
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('delivery_sdate')
                                {{ $message }}
                            @enderror
                            @error('delivery_edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $searchParam['data_per_page'] }}" />
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>

    <div class="card shadow p-4 mb-4">
        <div class="row justify-content-end mb-4">
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

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList small mb-0">
                <thead class="align-middle">
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <th scope="col" style="width:40px" class="text-center">編輯</th>
                        <td scope="col" class="wrap lh-sm">
                            <div class="fw-bold">出貨單號</div>
                            <div>單據編號</div>
                        </td>
                        <th scope="col">訂單金額</th>
                        <th scope="col">寄件倉</th>
                        <th scope="col">訂單狀態</th>
                        <th scope="col">物流狀態</th>
                        <th scope="col">物流分類</th>
                        <th scope="col">物流溫層</th>
                        <th scope="col">自取倉溫層</th>
                        <th scope="col">寄件人姓名</th>
                        <th scope="col">收件人姓名</th>
                        <th scope="col">收件人地址</th>
                        <th scope="col">產品名稱</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($uniqueSubOrderDataList as $key => $data)
                        <tr>
                            <th scope="row" class="fs-6">{{ $key + 1 }}</th>
                            <td class="text-center fs-6">
                                @can('cms.delivery.edit')
                                    <a href="
                                    @if ($data->event == App\Enums\Delivery\Event::order()->value) {{ Route('cms.order.detail', ['id' => $data->order_id, 'subOrderId' => $data->sub_order_id], true) }}
                                @elseif($data->event == App\Enums\Delivery\Event::consignment()->value)
                                {{ Route('cms.consignment.edit', ['id' => $data->event_id], true) }}
                                @elseif($data->event == App\Enums\Delivery\Event::csn_order()->value)
                                {{ Route('cms.consignment-order.edit', ['id' => $data->event_id], true) }} @endif"
                                        data-bs-toggle="tooltip" title="編輯"
                                        class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                @endcan
                            </td>
                            <td class="wrap">
                                <div class="fw-bold">{{ $data->delivery_sn }}</div>
                                <div>{{ $data->event_sn }}</div>
                            </td>
                            <td>${{ number_format($data->total_price) }}</td>
                            <td class="wrap">{{ $data->depot_name ?? '-' }}</td>
                            <td @class(['fs-6', 'text-danger' => $data->order_status === '取消'])>
                                {{ $data->order_status }}
                            </td>
                            <td class="fs-6">{{ $data->logistic_status }}</td>
                            <td class="wrap">
                                <div class="lh-1 text-nowrap">
                                    <span @class([
                                        'badge -badge',
                                        '-primary' => $data->ship_category_name === '宅配',
                                        '-warning' => $data->ship_category_name === '自取',
                                    ])>{{ $data->ship_category_name }}</span>
                                </div>
                                <div class="lh-base text-nowrap">{{ $data->method ?? '-' }}</div>
                            </td>
                            <td>{{ $data->temp_name }}</td>
                            <td>{{ $data->depot_temp_name }}</td>
                            <td class="wrap">{{ $data->sed_name }}</td>
                            <td class="wrap">{{ $data->rec_name }}</td>
                            <td>{{ $data->rec_address }}</td>
                            <td class="py-0 lh-base">
                                <ul class="list-group list-group-flush">
                                @foreach($data->productTitles as $productTitle)
                                    <li class="list-group-item bg-transparent pe-1">{{ $productTitle->product_title }}</li>
                                @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
        </script>
    @endpush
@endOnce
