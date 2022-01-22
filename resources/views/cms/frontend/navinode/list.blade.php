@extends('layouts.main')
@section('sub-content')
@if (is_null($prev))
    <h2 class="mb-4">選單列表設定</h2>
@else
    <div class="col mb-4">
        <a href="{{ Route('cms.navinode.index', ['level' => $prev]) }}" class="btn btn-primary">
            <i class="bi bi-arrow-left"></i> 返回上一階層
        </a>
    </div>
@endif
<form method="GET" action="{{ Route('cms.navinode.sort', ['level' => $level]) }}">
    <div class="card shadow p-4 mb-4">
        <div class="col mb-4">
            <a href="{{ Route('cms.navinode.create', ['level' => $level]) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> 新增選單
            </a>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-hover tableList">
                <thead>
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">名稱</th>
                        <th scope="col">階層</th>
                        <th scope="col">網址</th>
                        <th scope="col">群組名稱</th>
                        <th scope="col" class="text-center">子階層</th>
                        <th scope="col" class="text-center">編輯</th>
                        <th scope="col" class="text-center">刪除</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $data->node_title }}</td>
                            <td>@if ($data->has_child == 0) 單層 @else 多階 @endif</td>
                            <td @class(['table-secondary' => $data->has_child == 1])>{{ $data->url }}</td>
                            <td @class(['table-secondary' => $data->has_child == 1])>{{ $data->group_title }}</td>
                            <td @class(['text-center', 'table-secondary' => $data->has_child == 0])>
                                @if ($data->has_child == 1)
                                    <a href="{{ Route('cms.navinode.index', ['level' => $level . '-' . $data->id]) }}"
                                        data-bs-toggle="tooltip" title="子階層"
                                        class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-diagram-2"></i>
                                    </a>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ Route('cms.navinode.edit', ['level' => $level, 'id' => $data->id]) }}"
                                    data-bs-toggle="tooltip" title="編輯"
                                    class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </td>
                            <td class="text-center">
                                <a href="javascript:void(0)"
                                    data-href="{{ Route('cms.navinode.delete', ['level' => $level, 'id' => $data->id]) }}"
                                    data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                    class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <input type="hidden" name="id[]" value="{{ $data->id }}">
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4"
                @if(!isset($dataList) || 0 >= count($dataList)) disabled @endif
            >儲存排序</button>
        </div>
    </div>
</form>

<!-- Modal -->
<x-b-modal id="confirm-delete">
    <x-slot name="name">刪除確認</x-slot>
    <x-slot name="body">刪除後將無法復原！確認要刪除？</x-slot>
    <x-slot name="foot">
        <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
    </x-slot>
</x-b-modal>
@endsection
@once
    @push('sub-styles')
    @endpush
    @push('sub-scripts')
        <script>
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
        </script>
    @endpush
@endOnce
