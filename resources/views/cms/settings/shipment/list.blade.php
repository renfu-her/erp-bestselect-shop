@extends('layouts.main')
@section('sub-content')
<h2 class="mb-4">物流運費管理</h2>
<form id="search" action="{{ Route('cms.shipment.index') }}" method="GET">
    <div class="card shadow p-4 mb-4">
        <h6>搜尋條件</h6>
        <div class="row">
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">物流名稱</label>
                <input class="form-control" type="text" name="shi_name" value="" placeholder="輸入物流名稱">
            </div>
            <fieldset class="col-12 col-sm-6 mb-3">
                <legend class="col-form-label p-0 mb-2">出貨方式</legend>
                <div class="px-1 pt-1">
                    @foreach ($shi_method as $method)
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input"
                                       name="shi_method"
                                       type="radio"
                                       value="{{ $method->id }}" >
                                {{ $method->method }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </fieldset>
            <fieldset class="col-12 col-sm-6 mb-3">
                <legend class="col-form-label p-0 mb-2">溫層</legend>
                <div class="px-1 pt-1">
                    @foreach ($shi_temps as $temps)
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input"
                                       name="shi_temps"
                                       type="radio"
                                       value="{{ $temps->id }}" >
                                {{ $temps->temps }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </fieldset>
            <fieldset class="col-12 col-sm-6 mb-3">
                <legend class="col-form-label p-0 mb-2">是否有設定廠商？</legend>
                <div class="px-1 pt-1">
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input class="form-check-input"
                                   name="has_supplier"
                                   type="radio"
                                   value="1" >
                            是
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input class="form-check-input"
                                   name="has_supplier"
                                   type="radio"
                                   value="0" >
                            否
                        </label>
                    </div>
                </div>
            </fieldset>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">廠商名稱</label>
                <input class="form-control" type="text" name="supplier" value="" placeholder="輸入廠商名稱">
            </div>
        </div>
        <div class="col">
            <input type="hidden" name="data_per_page" value="{{ $data_per_page }}" />
            <button type="submit" class="btn btn-primary px-4">搜尋</button>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

    </div>
</form>
<div class="card shadow p-4 mb-4">
    <div class="row mb-4">
        <div class="col">
            @can('cms.shipment.create')
            <a href="{{ Route('cms.shipment.create', null, true) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> 新增物流運費
            </a>
            @endcan
        </div>
        {{-- <div class="col-auto">
            顯示
            <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                @foreach (config('global.dataPerPage') as $value)
                    <option value="{{ $value }}" @if ($data_per_page == $value) selected @endif>{{ $value }}</option>
                @endforeach
            </select>
            筆
        </div> --}}
    </div>
    <ul class="nav nav-tabs">
        @foreach ($categories as $key => $category)
            @if($category->category === '全家')
                <li class="nav-item">
                    <a class="nav-link disabled">
                        全家(待串接開發)
                    </a>
                </li>
            @elseif($category->category === '自取')
            @else
                <li class="nav-item">
                    <a class="nav-link {{ isActive($category->id, $currentCategoryId) }} "
                       href="{{ Route('cms.shipment.category', ['categoryId' => $category->id], true) }}">
                        {{ $category->category }}
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
    <div class="table-responsive tableOverBox">
        <table class="table table-striped tableList">
            <thead>
                <tr>
                    <th scope="col" style="width:3rem;">#</th>
                    <th scope="col">物流名稱（廠商名稱）</th>
                    <th scope="col">溫層</th>
                    <th scope="col">出貨方式</th>
                    <th scope="col" class="text-center">編輯</th>
                    <th scope="col" class="text-center">刪除</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($uniqueDataList as $key => $uData)
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>
                            {{ $uData->name }}
                            @if($uData->supplier)
                                {{ '（' .  $uData->supplier . '）' }}
                            @endif
                        </td>
                        <td @class([
                            'table-warning' => $uData->temps === '常溫',
                            'table-success' => $uData->temps === '冷藏',
                            'table-primary' => $uData->temps === '冷凍'
                        ])>{{ $uData->temps }}</td>
                        <td>{{ $uData->method }}</td>
                        <td class="text-center">
                            @can('cms.shipment.edit')
                            <a href="{{ Route('cms.shipment.edit', ['groupId' => $uData->group_id_fk], true) }}"
                                data-bs-toggle="tooltip" title="編輯"
                                class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            @endcan
                        </td>
                        <td class="text-center">
                            @can('cms.shipment.delete')
                            <a href="javascript:void(0)" data-href="{{ Route('cms.shipment.delete', ['groupId' => $uData->group_id_fk], true) }}"
                                data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                <i class="bi bi-trash"></i>
                            </a>
                            @endcan
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="6" class="pt-0 ps-0">
                            <table class="table mb-0 table-bordered table-sm">
                                <thead>
                                    <tr class="border-top-0" style="border-bottom-color:var(--bs-secondary);">
                                        <td>消費金額</td>
                                        <td class="text-end" style="width:20%;">運費</td>
                                        <td class="text-end" style="width:20%;">成本</td>
                                        <td class="text-end" style="width:20%;">最多件數</td>
                                    </tr>
                                </thead>
                                <tbody class="border-top-0">
                                    @foreach ($uData->group as $rule)
                                        <tr>
                                            <td>$ {{ number_format($rule->min_price) }} ~
                                                @if ($rule->is_above == 'true') 以上
                                                @else $ {{ number_format($rule->max_price) }} @endif
                                            </td>
                                            <td class="text-end">$ {{ number_format($rule->dlv_fee) }}</td>
                                            <td class="text-end">$ {{ number_format($rule->dlv_cost) }}</td>
                                            <td class="text-end">{{ $rule->at_most }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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


<!-- Modal -->
<x-b-modal id="confirm-delete">
    <x-slot name="title">刪除此「物流群組」確認</x-slot>
    <x-slot name="body">刪除後，此「物流群組」將無法復原！確認要刪除？</x-slot>
    <x-slot name="foot">
        <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
    </x-slot>
</x-b-modal>

@endsection

@once
    @push('sub-scripts')
        <script>
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });

            // $('#dataPerPageElem').on('change', function(e) {
            //     $('input[name=data_per_page]').val($(this).val());
            //     $('#search').submit();
            // });
        </script>
    @endpush
@endonce
