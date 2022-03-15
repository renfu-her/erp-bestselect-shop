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
                    <select class="form-select -select2 -multiple" multiple name="receive_depot_id[]" aria-label="由哪一個倉庫出貨" data-placeholder="多選">
                        @foreach ($depotList as $key => $data)
                            <option value="{{ $data->id }}"
                                    @if (in_array($data->id, $searchParam['receive_depot_id'] ?? [])) selected @endif>{{ $data->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">物流分類</label>
                    <select class="form-select -select2 -multiple" multiple name="ship_method[]" aria-label="物流分類" data-placeholder="多選">
                        <option value="喜鴻出貨" @if (in_array('喜鴻出貨', $searchParam['ship_method'] ?? []) || ($searchParam['ship_method'] == [])) selected @endif>喜鴻出貨</option>
                        <option value="廠商出貨" @if (in_array('廠商出貨', $searchParam['ship_method'] ?? [])) selected @endif>廠商出貨</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">物態</label>
                    <select class="form-select -select2 -multiple" multiple name="logistic_status_id[]" aria-label="物態" data-placeholder="多選">
                        @foreach ($logisticStatus as $key => $data)
                            <option value="{{ $data->id }}"
                                    @if (in_array($data->id, $searchParam['logistic_status_id'] ?? [])) selected @endif>{{ $data->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">訂單起訖日期</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('order_sdate') is-invalid @enderror"
                               name="order_sdate" value="{{ $searchParam['order_sdate'] }}" aria-label="訂單起始日期" />
                        <input type="date" class="form-control -endDate @error('order_edate') is-invalid @enderror"
                               name="order_edate" value="{{ $searchParam['order_edate'] }}" aria-label="訂單結束日期" />
                        <button class="btn btn-outline-secondary icon" type="button" data-clear
                                data-bs-toggle="tooltip" title="清空日期"><i class="bi bi-calendar-x"></i>
                        </button>
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
                        <button class="btn btn-outline-secondary icon" type="button" data-clear
                                data-bs-toggle="tooltip" title="清空日期"><i class="bi bi-calendar-x"></i>
                        </button>
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
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">出貨單號</th>
                        <th scope="col">單據編號</th>
                        <th scope="col">寄件倉</th>
                        <th scope="col">物態</th>
                        <th scope="col">物流分類</th>
                        <th scope="col">寄件人姓名</th>
                        <th scope="col">收件人姓名</th>
                        <th scope="col">收件人地址</th>
                        <th scope="col" class="text-center">編輯</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $data->delivery_sn }}</td>
                            <td>{{ $data->event_sn }}</td>
                            <td>{{ $data->depot_name }}</td>
                            <td>{{ $data->logistic_status }}</td>
                            <td>{{ $data->method }}</td>
                            <td>{{ $data->rec_name }}</td>
                            <td>{{ $data->ord_name }}</td>
                            <td>{{ $data->ord_address }}</td>
                            <td class="text-center">
                                @if($data->event == App\Enums\Delivery\Event::order()->value)
                                    <a href="{{ Route('cms.order.detail', ['id' => $data->order_id, 'subOrderId' => $data->delivery_id], true) }}"
                                       data-bs-toggle="tooltip" title="編輯"
                                       class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                @endif
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
    @push('sub-styles')
        <style>
            .icon.-close_eye+span.label::before {
                content: '不';
            }

        </style>
    @endpush
    @push('sub-scripts')
        <script>
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
        </script>
    @endpush
@endOnce
