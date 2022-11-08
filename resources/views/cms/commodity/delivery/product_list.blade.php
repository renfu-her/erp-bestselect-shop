@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">出貨管理</h2>

    <form id="search" action="{{ Route('cms.delivery.product_list') }}" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">廠商</label>
                    <select class="form-select -select2 -multiple" multiple name="search_supplier[]" aria-label="廠商"
                            data-placeholder="多選">
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier['id'] }}" @if (in_array($supplier['id'], $searchParam['search_supplier'])) selected @endif>
                                {{ $supplier['name'] }}（{{ $supplier['vat_no'] }}）
                            </option>
                        @endforeach
                    </select>
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

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">商品名稱</label>
                    <input class="form-control" value="{{ $searchParam['keyword'] }}" type="text" name="keyword"
                        placeholder="商品名稱">
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
                        <option value="{{ $value }}" @if ($data_per_page == $value) selected @endif>
                            {{ $value }}</option>
                    @endforeach
                </select>
                筆
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="small align-middle">
                <tr>
                    <th scope="col" style="width:40px">#</th>
                    <th scope="col" style="width:40px" class="text-center">編輯</th>
                    <th scope="col">商品名稱</th>
                    <th scope="col">出貨數量</th>
                    <th scope="col">採購單號</th>
                    <th scope="col">出貨單號</th>
                    <th scope="col">出貨日期</th>
                    <th scope="col">商品負責人</th>
                    @if(null != $searchParam['search_supplier'])
                        <th scope="col">廠商</th>
                    @endif
                    <th scope="col">出貨人員</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($dataList as $key => $data)
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td class="text-center fs-6">
                            @can('cms.delivery.edit')
                                <a href="
                                    @if ($data->event == App\Enums\Delivery\Event::order()->value) {{ Route('cms.order.detail', ['id' => $data->order_id, 'subOrderId' => $data->event_id], true) }}
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
                        @php
                            $rcv_depot_data = (null != $data->rcv_depot_data)? json_decode($data->rcv_depot_data): null;
                        @endphp

                        <td class="py-0 lh-base">
                            <ul class="list-group list-group-flush">
                                @if(null != $rcv_depot_data && 0 < count($rcv_depot_data))
                                    @foreach ($rcv_depot_data as $item_data)
                                        <li class="list-group-item bg-transparent pe-1">{{ $item_data->product_title }}</li>
                                    @endforeach
                                @endif
                            </ul>
                        </td>
                        <td class="py-0 lh-base">
                            <ul class="list-group list-group-flush">
                                @if(null != $rcv_depot_data && 0 < count($rcv_depot_data))
                                    @foreach ($rcv_depot_data as $item_data)
                                        <li class="list-group-item bg-transparent pe-1">{{ $item_data->qty }}</li>
                                    @endforeach
                                @endif
                            </ul>
                        </td>

                        <td>{{ $data->event_sn }}</td>
                        <td>{{ $data->sn }}</td>
                        <td>{{ $data->audit_date }}</td>

                        <td class="py-0 lh-base">
                            <ul class="list-group list-group-flush">
                                @if(null != $rcv_depot_data && 0 < count($rcv_depot_data))
                                    @foreach ($rcv_depot_data as $item_data)
                                        <li class="list-group-item bg-transparent pe-1">{{ $item_data->prd_user_name }}</li>
                                    @endforeach
                                @endif
                            </ul>
                        </td>

                        @if(null != $searchParam['search_supplier'])
                        <td class="py-0 lh-base">
                            <ul class="list-group list-group-flush">
                                @if(null != $rcv_depot_data && 0 < count($rcv_depot_data))
                                    @foreach ($rcv_depot_data as $item_data)
                                        <li class="list-group-item bg-transparent pe-1">{{ $item_data->supplier_name }}</li>
                                    @endforeach
                                @endif
                            </ul>
                        </td>
                        @endif
                        <td>{{ $data->audit_user_name }}</td>
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
    @push('sub-scripts')
        <script>
            // 顯示筆數
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
        </script>
    @endpush
@endOnce
