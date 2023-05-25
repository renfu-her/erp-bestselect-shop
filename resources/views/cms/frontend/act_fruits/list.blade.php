@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">活動-四季鮮果</h2>

    <div class="card shadow p-4 mb-4">
        <div class="row mb-4">
            <div class="col-auto">
                @can('cms.act-fruits.create')
                    <a href="{{ Route('cms.act-fruits.create', null, true) }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> 新增水果
                    </a>
                @endcan
                <a href="{{ Route('cms.act-fruits.season') }}" class="btn btn-success">水果分類設定</a>
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="align-middle">
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">名稱</th>
                        <th scope="col">產季</th>
                        <th scope="col">目前狀態</th>
                        <th scope="col" width="60" class="text-center">編輯</th>
                        <th scope="col" width="60" class="text-center">刪除</th>
                    </tr>
                </thead>
                <tbody>
                {{-- @foreach ($dataList as $key => $data) --}}
                    <tr>
                        <th scope="row">1</th>
                        <td>茂谷柑</td>
                        <td>春、冬季 (12月-隔年3月)</td>
                        <td>12月開放預購</td>
                        <td class="text-center">
                            @can('cms.act-fruits.edit')
                                <a href="{{ Route('cms.act-fruits.edit', ['id' => 1], true) }}"
                                   data-bs-toggle="tooltip" title="編輯"
                                   class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                        </td>
                        <td class="text-center">
                            @can('cms.act-fruits.delete')
                                <a href="javascript:void(0)"
                                   data-href="#"
                                   data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                   class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                    <i class="bi bi-trash"></i>
                                </a>
                            @endcan
                        </td>
                    </tr>
                {{-- @endforeach --}}
                </tbody>
            </table>
        </div>
    </div>

    {{-- @if ($dataList->hasPages()) --}}
        <div class="row flex-column-reverse flex-sm-row mb-4">
            <div class="col d-flex justify-content-end align-items-center">
                {{-- 頁碼 --}}
                {{-- <div class="d-flex justify-content-center">{{ $dataList->links() }}</div> --}}
            </div>
        </div>
    {{-- @endif --}}

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
            $('#confirm-delete').on('show.bs.modal', function (e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });

        </script>
    @endpush
@endOnce
