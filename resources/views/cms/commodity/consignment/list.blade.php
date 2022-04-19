@extends('layouts.main')
@section('sub-content')

    <h2 class="mb-4">寄倉管理</h2>

    <form id="search" action="{{ Route('cms.consignment.index') }}" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label class="form-label">寄倉單號</label>
                    <input class="form-control" name="consignment_sn" type="text" placeholder="寄倉單號" value="{{$consignment_sn}}"
                           aria-label="採購單號">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">出貨倉庫</label>
                    <select class="form-select" name="send_depot_id" aria-label="出貨倉庫">
                        <option value="" @if ('' == $send_depot_id ?? '') selected @endif disabled>請選擇</option>
                        <@foreach ($depotList as $key => $data)
                            <option value="{{ $data->id }}"
                                    @if ($data->id == $send_depot_id ?? '') selected @endif>{{ $data->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">入庫倉庫</label>
                    <select class="form-select" name="receive_depot_id" aria-label="入庫倉庫">
                        <option value="" @if ('' == $receive_depot_id ?? '') selected @endif disabled>請選擇</option>
                        <@foreach ($depotList as $key => $data)
                            <option value="{{ $data->id }}"
                                    @if ($data->id == $receive_depot_id ?? '') selected @endif>{{ $data->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">寄倉單日期起迄</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('csn_sdate') is-invalid @enderror"
                               name="csn_sdate" value="{{ $csn_sdate }}" aria-label="寄倉單起始日期" />
                        <input type="date" class="form-control -endDate @error('csn_edate') is-invalid @enderror"
                               name="csn_edate" value="{{ $csn_edate }}" aria-label="寄倉單結束日期" />
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('csn_sdate')
                            {{ $message }}
                            @enderror
                            @error('csn_edate')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
                <fieldset class="col-12 mb-3">
                    <legend class="col-form-label p-0 mb-2">審核狀態</legend>
                    <div class="px-1 pt-1">
                        @foreach (App\Enums\Consignment\AuditStatus::asArray() as $key => $val)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="audit_status" type="radio"
                                           value="{{ $val }}" @if (old('audit_status', $audit_status ?? null) == $val) checked @endif>
                                    {{ App\Enums\Consignment\AuditStatus::getDescription($val) }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
                <div class="col-12 col-md-6 mb-3">
                    <label class="form-label" for="iStatus">入庫狀態</label>
                    <div class="input-group">
                        <select id="iStatus" class="form-select" aria-label="入庫狀態">
                            <option value="" selected>請選擇</option>
                            @foreach ($all_inbound_status as $key => $data)
                                <option value="{{ $key }}">{{ $data }}</option>
                            @endforeach
                        </select>
                        <button id="clear_iStatus" class="btn btn-outline-secondary" type="button" data-bs-toggle="tooltip" title="清空">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <input type="hidden" name="inbound_status" value="{{ $inbound_status }}">
                    <div id="chip-group-iStatus" class="d-flex flex-wrap bd-highlight chipGroup"></div>
                </div>
            </div>

            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $data_per_page }}" />
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>
    <form id="actionForms">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="row justify-content-end mb-4">
                <div class="col">
                    @can('cms.consignment.create')
                        <a href="{{ Route('cms.consignment.create', null, true) }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg pe-1"></i> 新增寄倉單
                        </a>
                    @endcan
                </div>
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
                        <th scope="col">寄倉單號</th>
                        <th scope="col">寄倉出貨單號</th>
                        <th scope="col">採購入庫單號</th>
                        <th scope="col">商品名稱</th>
                        <th scope="col">SKU碼</th>
                        <th scope="col">審核狀態</th>
                        <th scope="col">寄倉日期</th>
                        <th scope="col">寄倉數量</th>
                        <th scope="col">入倉狀態</th>
                        <th scope="col">出貨倉庫</th>
                        <th scope="col">入庫倉庫</th>

                        <th scope="col" class="text-center">編輯</th>
                        <th scope="col" class="text-center">刪除</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($dataList)
                        @foreach ($dataList as $key => $data)
                            <tr>
                                <th scope="row">{{ $key + 1 }}</th>
                                <td>{{ $data->consignment_sn }}</td>
                                <td>{{ $data->dlv_sn }}</td>
                                <td>{{ $data->origin_inbound_sn }}</td>
                                <td>{{ $data->title }}</td>
                                <td>{{ $data->sku }}</td>
                                <td>{{ $data->audit_status }}</td>
                                <td>{{ $data->created_at }}</td>
                                <td>{{ $data->num }}</td>
                                <td>{{ $data->inbound_type }}</td>
                                <td>{{ $data->send_depot_name }}</td>
                                <td>{{ $data->receive_depot_name }}</td>

                                <td class="text-center">
                                    @can('admin.consignment.edit')
                                    <a href="{{ Route('cms.consignment.edit', ['id' => $data->consignment_id], true) }}"
                                       data-bs-toggle="tooltip" title="編輯"
                                       class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    @endcan
                                </td>
                                <td class="text-center">
                                    @can('admin.consignment.delete')
                                    <a href="javascript:void(0)" data-href="{{ Route('cms.consignment.delete', ['id' => $data->consignment_id], true) }}"
                                       data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                       class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row flex-column-reverse flex-sm-row">
            <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
                @if($dataList)
                    <div class="mx-3">共 {{ $dataList->lastPage() }} 頁(共找到 {{ $dataList->total() }} 筆資料)</div>
                     頁碼
                    <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
                @endif
            </div>
        </div>
    </form>

    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">刪除後將無法復原！確認要刪除？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
        </x-slot>
    </x-b-modal>

@endsection

@once
    @push('sub-scripts')
        <script>
            // 顯示筆數選擇
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });

            // region
            let selectStatus = $('input[name="inbound_status"]').val();
            let all_inbound_status = @json($all_inbound_status);
            let Chips_regions = new ChipElem($('#chip-group-iStatus'));
            // init
            selectStatus = Chips_regions.init(selectStatus, all_inbound_status);
            // bind
            $('#iStatus').off('change.chips').on('change.chips', function(e) {
                let region = { val: $(this).val(), title: $(this).children(':selected').text()};
                if (selectStatus.indexOf(region.val) === -1) {
                    selectStatus.push(region.val);
                    Chips_regions.add(region.val, region.title);
                }

                $(this).val('');
            });
            $('#search').on('submit', function(e) {
                $('input[name="inbound_status"]').val(selectStatus);
            });
            // X btn
            Chips_regions.onDelete = function(id) {
                selectStatus.splice(selectStatus.indexOf(id), 1);
            };
            // 清空
            $('#clear_iStatus').on('click', function(e) {
                selectStatus = [];
                Chips_regions.clear();
                e.preventDefault();
            });
        </script>
    @endpush
@endonce
