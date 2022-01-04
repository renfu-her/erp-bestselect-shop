@extends('layouts.main')
@section('sub-content')
<h2 class="mb-4">物流運費設定</h2>
<div class="card shadow p-4 mb-4">
    <div class="row mb-4">
        <div class="col">
            @can('cms.shipment.create')
            <a href="{{ Route('cms.shipment.create', null, true) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> 新增物流運費
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
        <table class="table tableList mb-0">
            <thead>
                <tr>
                    <th scope="col">快遞物流名稱</th>
                    <th scope="col">最低消費金額</th>
                    <th scope="col">最高消費金額</th>
                    <th scope="col">運送溫度</th>
                    <th scope="col">出貨方式</th>
                    <th scope="col">運費</th>
                    <th scope="col">成本</th>
                    <th scope="col">最多件數</th>
                    <th scope="col" class="text-center">編輯</th>
                    <th scope="col" class="text-center">刪除</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dataList as $key => $data)
                    <tr
                        @if(array_search($data->group_id_fk, $groupIdColorIndex) % 2 == 0)
                            class="table-primary"
                        @endif
                    >
                        <td>{{ $data->name }}</td>
                        <td>{{ $data->min_price }}</td>
                        <td>{{ $data->max_price }}</td>
                        <td>{{ $data->temps }}</td>
                        <td>{{ $data->method }}</td>
                        <td>{{ $data->dlv_fee }}</td>
                        <td>{{ $data->dlv_cost }}</td>
                        <td>{{ $data->at_most }}</td>
                        <td class="text-center">
                            @can('cms.shipment.edit')
                            <a href="{{ Route('cms.shipment.edit', ['groupId' => $data->group_id_fk], true) }}"
                                data-bs-toggle="tooltip" title="編輯"
                                class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            @endcan
                        </td>
                        <td class="text-center">
                            @can('cms.shipment.delete')
                            <a href="javascript:void(0)" data-href="{{ Route('cms.shipment.delete', ['groupId' => $data->group_id_fk], true) }}"
                                data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                <i class="bi bi-trash"></i>
                            </a>
                            @endcan
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
    <x-slot name="title">刪除此「快遞物流群組」確認</x-slot>
    <x-slot name="body">刪除後，此「快遞物流群組」將無法復原！確認要刪除？</x-slot>
    <x-slot name="foot">
        <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
    </x-slot>
</x-b-modal>

@endsection

@once
    @push('scripts')
        <script>
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
        </script>
    @endpush
@endonce
