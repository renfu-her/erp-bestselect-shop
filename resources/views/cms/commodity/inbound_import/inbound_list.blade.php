@extends('layouts.main')
@section('sub-content')

    <ul class="nav pm_navbar">
        <li class="nav-item">
            <a class="nav-link" href="{{ Route('cms.inbound_import.index', [], true) }}">上傳檔案</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ Route('cms.inbound_import.import_log', [], true) }}">匯入紀錄</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="{{ Route('cms.inbound_import.inbound_list', [], true) }}">入庫單列表</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ Route('cms.inbound_import.inbound_log', [], true) }}">入庫單調整紀錄</a>
        </li>
    </ul>
    <hr class="narbarBottomLine mb-3">

    <form id="search" action="{{ Route('cms.inbound_import.inbound_list') }}" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">搜尋商品</label>
                    <input class="form-control" value="{{ $searchParam['title'] }}" type="text" name="title"
                           placeholder="輸入商品名稱">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">採購單號</label>
                    <input class="form-control" value="{{ $searchParam['purchase_sn'] }}" type="text" name="purchase_sn"
                           placeholder="輸入採購單號">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">入庫單號</label>
                    <input class="form-control" value="{{ $searchParam['inbound_sn'] }}" type="text" name="inbound_sn"
                           placeholder="輸入入庫單號">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">盤點狀態</legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="inventory_status" type="radio"
                                       value="all" @if ($searchParam['inventory_status'] == 'all') checked @endif>
                                全部
                            </label>
                        </div>
                        @foreach (App\Enums\Consignment\AuditStatus::asArray() as $key => $val)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="inventory_status" type="radio"
                                           value="{{ $val }}" @if ($searchParam['inventory_status'] == $val) checked @endif>
                                    {{ App\Enums\Consignment\AuditStatus::getDescription($val) }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">入庫人員</label>
                    <select class="form-select -select2 -multiple" multiple name="inbound_user_id[]" aria-label="入庫人員" data-placeholder="多選">
                        @foreach ($userList as $key => $data)
                            <option value="{{ $data->id }}"
                                    @if (in_array($data->id, $searchParam['inbound_user_id'] ?? []))) selected @endif>{{ $data->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">新增起訖日期</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('inbound_sdate') is-invalid @enderror"
                               name="inbound_sdate" value="{{ $searchParam['inbound_sdate'] }}" aria-label="新增起始日期" />
                        <input type="date" class="form-control -endDate @error('inbound_edate') is-invalid @enderror"
                               name="inbound_edate" value="{{ $searchParam['inbound_edate'] }}" aria-label="新增結束日期" />
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('inbound_sdate')
                            {{ $message }}
                            @enderror
                            @error('inbound_edate')
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
            <table class="table table-striped tableList table-sm small">
                <thead class="align-middle">
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <td scope="col" class="wrap">
                            <div class="text-nowrap fw-bold">盤點狀態</div>
                            <div class="text-nowrap">盤點人員</div>
                        </td>
                        <th scope="col" style="width:40px" class="text-center">編輯</th>
                        <td scope="col" class="wrap">
                            <div class="fw-bold">採購單號</div>
                            <div>入庫單</div>
                        </td>
                        <th scope="col">商品款式</th>
                        <th scope="col" class="wrap lh-1 text-center">庫存剩餘數量</th>
                        <th scope="col" class="text-end">單價</th>
                        <th scope="col">效期</th>
                        <th scope="col">倉庫</th>
                        <th scope="col">入庫人員</th>
                        <th scope="col">新增日期</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td class="wrap">
                                <div>{{ $data->inventory_status_str }}</div>
                                <div>{{ $data->inventory_create_user_name }}</div>
                            </td>
                            <td>
                                @can('cms.inbound_import.edit')
                                <a href="{{ Route('cms.inbound_import.inbound_edit', ['inboundId' => $data->inbound_id], true) }}"
                                   data-bs-toggle="tooltip" title="編輯"
                                   class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                @endcan
                            </td>
                            <td class="wrap">
                                <div class="fw-bold">
                                    <a href="@if(\App\Enums\Delivery\Event::purchase()->value == $data->event)
                                        {{ route('cms.purchase.edit', ['id' => $data->event_id]) }}
                                        @elseif(\App\Enums\Delivery\Event::ord_pickup()->value == $data->event)
                                        {{ route('cms.order.detail', ['id' => $data->event_id]) }}
                                        @elseif(\App\Enums\Delivery\Event::consignment()->value == $data->event)
                                        {{ route('cms.consignment.edit', ['id' => $data->event_id]) }}
                                        @endif" target="_blank">{{ $data->event_sn }}</a>
                                </div>
                                <div>{{ $data->inbound_sn ?? '-' }}</div>
                            </td>
                            <td class="wrap">
                                <div class="lh-1 text-nowrap text-secondary">{{ $data->style_sku }}</div>
                                <div class="lh-lg">{{ $data->product_title }}</div>
                            </td>
                            <td class="text-end">{{ number_format($data->qty) }}</td>
                            <td class="text-end">{{ ($data->unit_cost) }}</td>
                            <td>{{ $data->expiry_date ? date('Y/m/d', strtotime($data->expiry_date)) : '' }}</td>
                            <td>{{ $data->depot_name }}</td>
                            <td>{{ $data->inbound_user_name }}</td>
                            <td>{{ $data->created_at }}</td>
{{--                            <td class="wrap" style="min-width:80px;">{{ $data->depot_name }}</td>--}}
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            @if($dataList)
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
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
        </script>
    @endpush
@endOnce
