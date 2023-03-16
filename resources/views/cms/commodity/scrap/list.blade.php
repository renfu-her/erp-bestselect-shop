@extends('layouts.main')
@section('sub-content')

    <h2 class="mb-4">報廢管理</h2>

    <form id="search" action="{{ Route('cms.scrap.index') }}" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label class="form-label">報廢單號</label>
                    <input class="form-control" name="scrap_sn" type="text" placeholder="報廢單號"
                           value="{{$searchParam['scrap_sn'] ?? ''}}"
                           aria-label="報廢單號">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">採購單號</label>
                    <input class="form-control" value="{{ $searchParam['purchase_sn'] ?? '' }}" type="text" name="purchase_sn"
                           placeholder="輸入採購單號">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">入庫單號</label>
                    <input class="form-control" value="{{ $searchParam['inbound_sn'] ?? '' }}" type="text" name="inbound_sn"
                           placeholder="輸入入庫單號">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">搜尋商品</label>
                    <input class="form-control" value="{{ $searchParam['keyword'] ?? '' }}" type="text" name="keyword"
                           placeholder="輸入商品名稱">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">倉庫</label>
                    <select class="form-select -select2 -multiple" multiple name="inbound_depot_id[]" aria-label="倉庫"
                            data-placeholder="多選">
                        @foreach ($depotList as $key => $data)
                            <option value="{{ $data->id }}" @if (in_array($data->id, $searchParam['inbound_depot_id'] ?? [])) selected @endif>
                                {{ $data->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">盤點狀態</legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="audit_status" type="radio"
                                       value="all" @if ($searchParam['audit_status'] == 'all') checked @endif>
                                全部
                            </label>
                        </div>
                        @foreach (App\Enums\Consignment\AuditStatus::asArray() as $key => $val)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="audit_status" type="radio"
                                           value="{{ $val }}" @if ($searchParam['audit_status'] == $val) checked @endif>
                                    {{ App\Enums\Consignment\AuditStatus::getDescription($val) }}
                                </label>
                            </div>
                        @endforeach
                    </div>
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
                    @can('cms.scrap.create')
                        <a href="{{ Route('cms.scrap.create', null, true) }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg pe-1"></i> 新增報廢單
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
                    <thead>
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <th scope="col" style="width:40px" class="text-center">編輯</th>
                        <th scope="col">報廢單號</th>
                        <th scope="col">報廢日期</th>
                        <th scope="col">新增人員</th>
                        <th scope="col">審核人員</th>
                        <th scope="col">審核狀態</th>
                        <th scope="col">備註</th>
                        <th scope="col">單號</th>

                        <th scope="col" class="text-center">刪除</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($dataList)
                        @foreach ($dataList as $key => $data)
                            <tr>
                                <th scope="row">{{ $key + 1 }}</th>
                                <td class="text-center">
                                    @can('cms.scrap.edit')
                                        <a href="{{ Route('cms.scrap.edit', ['id' => $data->id], true) }}"
                                           data-bs-toggle="tooltip" title="編輯"
                                           class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endcan
                                </td>
                                <td>{{ $data->sn }}</td>
                                <td>{{ date('Y/m/d', strtotime($data->created_at)) }}</td>
                                <td>{{ $data->user_name }}</td>
                                <td>{{ $data->audit_user_name }}</td>
                                <td>{{ \App\Enums\Consignment\AuditStatus::getDescription($data->audit_status) }}</td>
                                <td>{{ $data->memo }}</td>
                                <td></td>

                                <td class="text-center">
                                    @can('cms.scrap.delete')
                                        @if(\App\Enums\Consignment\AuditStatus::approved() != $data->audit_status)
                                            <a href="javascript:void(0)"
                                               data-href="{{ Route('cms.scrap.delete', ['id' => $data->id], true) }}"
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
                                            <td scope="col" class="wrap">
                                                <div class="fw-bold">採購單號</div>
                                                <div>入庫單</div>
                                            </td>
                                            <th scope="col">商品名稱</th>
                                            <td scope="col" class="wrap">
                                                <div class="fw-bold">事件</div>
                                                <div>倉庫</div>
                                            </td>
                                            <th scope="col">報廢數量</th>
                                            <th scope="col">備註</th>
                                        </tr>
                                        </thead>
                                        <tbody class="border-top-0">
                                        @php
                                            $itemsConcat = (null != $data->groupConcat)? json_decode($data->groupConcat): null;
                                        @endphp
                                        @if(null != $itemsConcat && 0 < count($itemsConcat))
                                            @foreach ($itemsConcat as $item_data)
                                                <tr>
                                                    <td class="wrap">
                                                        <div class="fw-bold">{{ $item_data->event_sn }}</div>
                                                        <div>{{ $item_data->inbound_sn ?? '-' }}</div>
                                                    </td>
                                                    <td class="wrap">
                                                        <div class="lh-1 small text-secondary" data-td="sku">{{ $item_data->sku }}</div>
                                                        <div class="lh-base" data-td="product_title">{{$item_data->product_title}}</div>
                                                        <div class="lh-1 small fw-light">
                                                            <span class="bg-secondary text-white px-1" data-td="expiry_date">效期：{{date('Y/m/d', strtotime($item_data->expiry_date))}}</span>
                                                        </div>
                                                    </td>

                                                    <td class="wrap">
                                                        <div class="lh-base text-nowrap">
                                                            <span class="fw-bold">{{$item_data->event_name}}</span>
                                                        </div>
                                                        <div class="lh-1" data-td="depot_name">{{$item_data->depot_name}}</div>
                                                    </td>
                                                    <td>{{ number_format($item_data->qty) }}</td>
                                                    <td>{{ $item_data->memo }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
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
            $('#dataPerPageElem').on('change', function (e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
            $('#confirm-delete').on('show.bs.modal', function (e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
        </script>
    @endpush
@endonce
