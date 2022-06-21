@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">分潤審核管理</h2>
    <form id="search" action="{{ $formAction }}" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">姓名</label>
                    <input class="form-control" type="text" name="name" placeholder="請輸入姓名" value="{{ $name }}"
                        aria-label="姓名">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">員編</label>
                    <input class="form-control" type="text" name="sn" placeholder="請輸入員編" value="{{ $sn }}"
                        aria-label="員編">
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">審核狀態</label>
                    <select class="form-select" name="status">
                        <option value="">全部</option>
                        @foreach ($status as $key => $value)
                            <option value="{{ $key }}">
                                {{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col">
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>
    <div class="card shadow p-4 mb-4">
        <div class="col mb-4">
         
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">姓名</th>
                        <th scope="col">會員編號</th>
                        <th scope="col">審核狀態</th>
                        <th scope="col" class="text-center">編輯</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $data->name }}</td>
                            <td>{{ $data->sn }}</td>
                            <td>{{ $data->status_title }}</td>
                            <td class="text-center">
                                <a href="{{ Route('cms.customer-profit.edit', ['id' => $data->id], true) }}"
                                    data-bs-toggle="tooltip" title="編輯"
                                    class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
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
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
        </script>
    @endpush
@endonce
