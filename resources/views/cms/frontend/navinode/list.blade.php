@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">階層管理</h2>
    <div class="card shadow p-4 mb-4">
        <div class="row justify-content-end mb-4">
            <div class="col">
                @if (!is_null($prev))
                    <a href="{{ Route('cms.navinode.index', ['level' => $prev]) }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> 返回上一階層
                    </a>
                @endif
                <a href="{{ Route('cms.product.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> 新增階層
                </a>
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">名稱</th>
                        <th scope="col">網址</th>
                        <th scope="col">群組名稱</th>
                        <th scope="col">階層</th>
                        <th scope="col">排序</th>
                        <th scope="col" class="text-center">編輯</th>
                        <th scope="col" class="text-center">子階層</th>
                        <th scope="col" class="text-center">刪除</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $data->node_title }}</td>
                            <td>{{ $data->url }}</td>
                            <td>{{ $data->group_title }}</td>
                            <td>@if ($data->has_child == 0) 單 @else 多階 @endif</td>
                            <td><input type="number" name="" value="{{ $data->sort }}"></td>
                            <td class="text-center">
                                <a href="{{ Route('cms.navinode.edit', ['level' => $level, 'id' => $data->id]) }}"
                                    data-bs-toggle="tooltip" title="編輯"
                                    class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </td>
                            <td>
                                @if ($data->has_child == 1)
                                    <a href="{{ Route('cms.navinode.index', ['level' => $level . '-' . $data->id]) }}"
                                        data-bs-toggle="tooltip" title="編輯"
                                        class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-diagram-2"></i>
                                    </a>
                                @endif
                            </td>
                            <td>
                                <a href="javascript:void(0)"
                                    data-href="{{ Route('cms.collection.delete', ['id' => $data->id], true) }}"
                                    data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                    class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
        </script>
    @endpush
@endOnce
