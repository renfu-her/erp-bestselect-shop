@extends('layouts.main')
@section('sub-content')

    <h2 class="mb-4">採購單管理</h2>

    <form id="search" action="{{ Route('cms.purchase.index') }}" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label class="form-label">採購單號</label>
                    <input class="form-control" name="purchase_sn" type="text" placeholder="採購單號" value="{{$purchase_sn}}"
                           aria-label="採購單號">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">採購人員</label>
                    <select class="form-select -select2 -multiple" multiple name="purchase_user_id[]" aria-label="採購人員" data-placeholder="多選">
                        @foreach ($userList as $key => $data)
                            <option value="{{ $data->id }}"
                                    @if (in_array($data->id, $purchase_user_id ?? []))) selected @endif>{{ $data->name }}</option>
                        @endforeach
                    </select>
                </div>
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
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">入庫人員</label>
                    <select class="form-select -select2 -multiple" multiple name="inbound_user_id[]" aria-label="入庫人員" data-placeholder="多選">
                        @foreach ($userList as $key => $data)
                            <option value="{{ $data->id }}"
                                    @if (in_array($data->id, $inbound_user_id ?? []))) selected @endif>{{ $data->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">廠商</label>
                    <select class="form-select -select2 -single" name="supplier_id" aria-label="採購廠商">
                        <option value="" selected disabled>請選擇</option>
                        @foreach ($supplierList as $supplierItem)
                            <option value="{{ $supplierItem->id }}"
                                    @if ($supplierItem->id == $supplier_id ?? '')) selected @endif>
                                {{ $supplierItem->name }}@if ($supplierItem->nickname)（{{ $supplierItem->nickname }}） @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">預計入庫倉</label>
                    <select class="form-select" name="estimated_depot_id" aria-label="預計入庫倉">
                        <option value="" @if ('' == $estimated_depot_id ?? '') selected @endif disabled>請選擇</option>
                        <@foreach ($depotList as $key => $data)
                            <option value="{{ $data->id }}"
                                    @if ($data->id == $estimated_depot_id ?? '') selected @endif>{{ $data->name }} {{ $data->can_tally ? '(理貨倉)' : '(非理貨倉)' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">倉庫</label>
                    <select class="form-select" name="depot_id" aria-label="倉庫">
                        <option value="" @if ('' == $depot_id ?? '') selected @endif disabled>請選擇</option>
                        <@foreach ($depotList as $key => $data)
                            <option value="{{ $data->id }}"
                                    @if ($data->id == $depot_id ?? '') selected @endif>{{ $data->name }} {{ $data->can_tally ? '(理貨倉)' : '(非理貨倉)' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">商品名稱</label>
                    <input class="form-control" name="title" type="text" placeholder="請輸入商品名稱或SKU" value="{{$title??''}}"
                           aria-label="商品名稱">
                </div>
                {{--            <div class="col-12 col-sm-6 mb-3">--}}
                {{--                <label class="form-label">SKU</label>--}}
                {{--                <input class="form-control" name="sku" type="text" placeholder="請輸入SKU碼" value="{{$sku??''}}"--}}
                {{--                       aria-label="SKU">--}}
                {{--            </div>--}}
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">效期</label>
                    <select class="form-select" name="expire_day" aria-label="效期">
                        <option value="" @if ('' == $expire_day ?? '') selected @endif>不限</option>
                        <option value="90" @if (90 == $expire_day ?? '') selected @endif>近90天</option>
                        <option value="60" @if (60 == $expire_day ?? '') selected @endif>近60天</option>
                        <option value="45" @if (45 == $expire_day ?? '') selected @endif>近45天</option>
                        <option value="30" @if (30 == $expire_day ?? '') selected @endif>近30天</option>
                        <option value="15" @if (15 == $expire_day ?? '') selected @endif>近15天</option>
                        <option value="7" @if (7 == $expire_day ?? '') selected @endif>近7天</option>
                        <option value="-1" @if (-1 == $expire_day ?? '') selected @endif>已過期</option>
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">採購起訖日期</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('purchase_sdate') is-invalid @enderror"
                               name="purchase_sdate" value="{{ $purchase_sdate }}" aria-label="採購起始日期" />
                        <input type="date" class="form-control -endDate @error('purchase_edate') is-invalid @enderror"
                               name="purchase_edate" value="{{ $purchase_edate }}" aria-label="採購結束日期" />
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('purchase_sdate')
                            {{ $message }}
                            @enderror
                            @error('purchase_edate')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">入庫起訖日期</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('') is-invalid @enderror"
                               name="inbound_sdate" value="{{ $inbound_sdate }}" aria-label="入庫起始日期" />
                        <input type="date" class="form-control -endDate @error('') is-invalid @enderror"
                               name="inbound_edate" value="{{ $inbound_edate }}" aria-label="入庫結束日期" />
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
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">顯示類型</legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="type" type="radio" value="0" @if (0 == $type ?? '' || '' == $type ?? '') checked @endif>
                                明細
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="type" type="radio" value="1" @if (1 == $type ?? '') checked @endif>
                                總表
                            </label>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="col-12 col-sm-6 mb-3">
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
                    @can('cms.purchase.create')
                        <a href="{{ Route('cms.purchase.create', null, true) }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg pe-1"></i> 新增採購單
                        </a>
                    @endcan
                </div>
                <div class="col-auto">
                    顯示
                    <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                        @foreach (config('global.dataPerPage_big') as $value)
                            <option value="{{ $value }}" @if ($data_per_page == $value) selected @endif>{{ $value }}</option>
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
                            <th scope="col">採購單號</th>
                            <th scope="col">商品名稱</th>
                            <th scope="col">採購日期</th>
                            <th scope="col">預計入庫倉</th>
                            <th scope="col">審核狀態</th>
                            <th scope="col">入庫狀態</th>
                            <th scope="col">訂金單號</th>
                            <th scope="col">尾款單號</th>

                            <th scope="col" class="text-center">刪除</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if($dataList)
                        @foreach ($dataList as $key => $data)
                            <tr>
                                <th scope="row">{{ $key + 1 }}</th>
                                <td class="text-center">
                                    @can('cms.purchase.edit')
                                    <a href="{{ Route('cms.purchase.edit', ['id' => $data->id], true) }}"
                                       data-bs-toggle="tooltip" title="編輯"
                                       class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    @endcan
                                </td>
                                <td>{{ $data->sn }}</td>
                                <td class="wrap">
                                    <div class="lh-1 small text-nowrap text-secondary">{{ $data->sku }}</div>
                                    <div class="lh-base">{{ $data->title }}</div>
                                </td>
                                <td>{{ date('Y/m/d', strtotime($data->created_at)) }}</td>
                                <td>{{ $data->estimated_depot_name }}</td>
                                <td>{{ $data->audit_status }}</td>
                                <td>{{ $data->inbound_status }}</td>
                                <td>{{ $data->deposit_num }}</td>
                                <td>{{ $data->final_pay_num }}</td>

                                <td class="text-center">
                                    @can('cms.purchase.delete')
                                        @if(\App\Enums\Consignment\AuditStatus::getDescription(\App\Enums\Consignment\AuditStatus::approved()) != $data->audit_status)
                                            <a href="javascript:void(0)" data-href="{{ Route('cms.purchase.delete', ['id' => $data->id], true) }}"
                                               data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                               class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td colspan="9" class="pt-0 ps-0 wrap">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="small table-light">
                                            <tr class="border-top-0" style="border-bottom-color:var(--bs-secondary);">
                                                <td scope="col">總價</td>
                                                <td scope="col">單價</td>
                                                <td scope="col">數量</td>
                                                <td scope="col">入庫數量</td>
                                                <td scope="col">異常數量</td>
                                                <td scope="col">效期</td>
                                                <td scope="col">採購人員</td>
                                                <td scope="col">入庫人員</td>
                                                <td scope="col">廠商</td>
                                                <td scope="col">發票號碼</td>
                                            </tr>
                                        </thead>
                                        <tbody class="border-top-0">
                                            <tr>
                                                <td>${{ number_format(floatval($data->price)) }}</td>
                                                <td>${{ number_format(floatval($data->single_price)) }}</td>
                                                <td>{{ number_format($data->num) }}</td>
                                                <td>{{ number_format($data->arrived_num) }}</td>
                                                <td>{{ $data->error_num }}</td>
                                                <td>{{ $data->expiry_date }}</td>
                                                <td>{{ $data->purchase_user_name }}</td>
                                                <td>{{ $data->inbound_user_names ?? '' }}</td>
                                                <td class="text-break">{{ $data->supplier_name }}</td>
                                                <td>{{ $data->invoice_num }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
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
                    {{-- 頁碼 --}}
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
