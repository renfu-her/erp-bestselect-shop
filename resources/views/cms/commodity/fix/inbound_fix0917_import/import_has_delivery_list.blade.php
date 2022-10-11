@extends('layouts.main')
@section('sub-content')

    <ul class="nav pm_navbar">
        <li class="nav-item">
            <a class="nav-link" aria-current="page" href="{{ Route('cms.inbound_fix0917_import.index', [], true) }}">上傳檔案</a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if(Route('cms.inbound_fix0917_import.import_no_delivery', [], true) == $formAction) active @endif"
               href="{{ Route('cms.inbound_fix0917_import.import_no_delivery', [], true) }}">0917前採購單尚未出貨</a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if(Route('cms.inbound_fix0917_import.import_has_delivery', [], true) == $formAction) active @endif"
               href="{{ Route('cms.inbound_fix0917_import.import_has_delivery', [], true) }}">0917前採購單已出貨</a>
        </li>
    </ul>
    <hr class="narbarBottomLine mb-3">

    <form id="search" action="{{ $formAction }}" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
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
            </div>

            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $searchParam['data_per_page'] }}" />
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>

    <div class="card shadow p-4 mb-4">
        <div class="row justify-content-end mb-4">
            @if(true == $showDelBtn)
            <div class="col-auto">
                <button disabled
                    data-bs-toggle="modal" data-bs-target="#confirm-delete"
                    class="btn btn-danger -multi-del">
                    多選刪除
                </button>
            </div>
            <div class="col align-self-center">
                已選擇 <span class="fw-bold -count">0</span> 筆資料
            </div>
            @endif
            <div class="col-auto">
                顯示
                <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                    @foreach (config('global.dataPerPage') as $value)
                        <option value="{{ $value }}" @if ($searchParam['data_per_page'] == $value) selected @endif>{{ $value }}</option>
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
                        @if(true == $showDelBtn)
                        <th scope="col">刪除 /
                            <div class="d-inline-block ms-1">
                                <label class="form-check-label">
                                    <input id="Del-select-all" class="form-check-input" type="checkbox" >
                                    全選
                                </label>
                            </div>
                        </th>
                        @endif
                        <th scope="col">採購單號</th>
                        <th scope="col">建立時間</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key+1 }}</th>
                            @if(true == $showDelBtn)
                            <td>
                                @can('cms.depot.delete')
                                    <a href="javascript:void(0)"
                                       data-href="{{ Route('cms.inbound_fix0917_import.del_purchase', ['purchaseID' => $data->id], true) }}"
                                       data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                       class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <div class="d-inline-block">
                                        <label class="form-check-label">
                                            <input class="form-check-input -del-select" type="checkbox" value="{{ $data->id }}" >
                                            選擇
                                        </label>
                                    </div>
                                @endcan
                            </td>
                            @endif

                            <td>
                                @can('cms.purchase.edit')
                                    <a href="{{ Route('cms.purchase.edit', ['id' => $data->id], true) }}">{{ $data->sn }}
                                    </a>
                                @endcan
                            </td>
                            <td>{{ $data->created_at ? date('Y/m/d', strtotime($data->created_at)) : '' }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td colspan="9" class="pt-0 ps-0 wrap">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead class="small table-light">
                                    <tr class="border-top-0" style="border-bottom-color:var(--bs-secondary);">
                                        <td scope="col">入庫單號</td>
                                        <td scope="col">名稱</td>
                                        <td scope="col">SKU</td>
                                        <td scope="col">倉庫</td>
                                        <td scope="col">入庫數量</td>
                                        <td scope="col">售出數量</td>
                                        <td scope="col">寄倉數量</td>
                                        <td scope="col">耗材數量</td>
                                    </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                    @if(isset($data->inbound_data) && 0 < count($data->inbound_data))
                                    @foreach($data->inbound_data as $key_ib => $val_ib)
                                        <tr>
                                            <td>{{ $val_ib->sn }}</td>
                                            <td>{{ $val_ib->title }}</td>
                                            <td>{{ $val_ib->sku }}</td>
                                            <td>{{ $val_ib->depot_name }}</td>
                                            <td>{{ $val_ib->inbound_num }}</td>
                                            <td>{{ $val_ib->sale_num }}</td>
                                            <td>{{ $val_ib->csn_num }}</td>
                                            <td>{{ $val_ib->consume_num }}</td>
                                        </tr>
                                    @endforeach
                                    @endif
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(true == $showDelBtn)
        <div class="row">
            <div class="col-auto">
                <button disabled
                    data-bs-toggle="modal" data-bs-target="#confirm-delete"
                    class="btn btn-danger -multi-del">
                    多選刪除
                </button>
            </div>
            <div class="col-auto align-self-center">
                已選擇 <span class="fw-bold -count">0</span> 筆資料
            </div>
            <div class="col-auto align-self-center mark">
                <i class="bi bi-exclamation-diamond-fill me-2 text-warning"></i>
                換頁刪除選擇不保留，請先執行多選刪除
            </div>
        </div>
        @endif

    </div>
    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            @if($dataList)
                <div class="mx-3">共 {{ $dataList->lastPage() }} 頁(共找到 {{ $dataList->total() }} 筆資料)</div>
                {{--頁碼--}}
                <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
            @endif
        </div>
    </div>

    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">刪除後將無法復原！確認要刪除？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
            <form id="multiForm" action="{{ Route('cms.inbound_fix0917_import.del_multi_purchase') }}" method="post" hidden>
                @csrf
            </form>
        </x-slot>
    </x-b-modal>
@endsection
@once
    @push('sub-scripts')
        <script>
            // 刪除
            $('#confirm-delete').on('show.bs.modal', function(e) {
                if ($(e.relatedTarget).hasClass('-del')) {
                    $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
                }
                if ($(e.relatedTarget).hasClass('-multi-del')) {
                    let del_item_id = [];
                    $('input[type="checkbox"]:checked.-del-select').each(function (index, element) {
                        // element == this
                        del_item_id.push($(element).val());
                    });
                    del_item_id = del_item_id.toString();
                    console.log('多刪on', del_item_id);
                    // 送form
                    const form = $('#multiForm');
                    form.append(`<input type="hidden" name="del_item_id" value="${del_item_id}">`);
                    $(this).find('.btn-ok').on('click.multi', function () {
                        form.submit();
                    });
                }
            });
            $('#confirm-delete').on('hidden.bs.modal', function (e) {
                $('#multiForm input[name="del_item_id"]').remove();
                $(this).find('.btn-ok').off('click.multi');
            });

            // 全選
            $('#Del-select-all').on('change', function () {
                const checked = $(this).prop('checked');
                $('input[type="checkbox"].-del-select').prop('checked', checked);
                const checked_n = $('input[type="checkbox"]:checked.-del-select').length;
                $('.-count').text(checked_n);
                $('.-multi-del').prop('disabled', checked_n === 0);
            });
            // 多選
            $('input[type="checkbox"].-del-select').on('change', function () {
                const checked_n = $('input[type="checkbox"]:checked.-del-select').length;
                $('.-count').text(checked_n);
                const n = $('input[type="checkbox"].-del-select').length;
                $('#Del-select-all').prop('checked', checked_n === n);
                $('.-multi-del').prop('disabled', checked_n === 0);
            });
        </script>
    @endpush
@endOnce
