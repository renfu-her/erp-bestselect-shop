@extends('layouts.main')
@section('sub-content')
<h2 class="mb-4">款式管理</h2>
<div class="card shadow p-4 mb-4">
    <div class="row mb-4">
        <div class="col">
            @can('cms.spec.create')
            <a href="{{ Route('cms.spec.create', null, true) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> 新增規格
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
                    <th scope="col">規格名稱</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dataList as $key => $data)
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $data->title }}</td>
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
{{--<x-b-modal id="confirm-delete">--}}
{{--    <x-slot name="title">刪除確認</x-slot>--}}
{{--    <x-slot name="body">刪除後將無法復原！確認要刪除？</x-slot>--}}
{{--    <x-slot name="foot">--}}
{{--        <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>--}}
{{--    </x-slot>--}}
{{--</x-b-modal>--}}

@endsection

{{--@once--}}
{{--    @push('sub-scripts')--}}
{{--        <script>--}}
{{--            $('#confirm-delete').on('show.bs.modal', function(e) {--}}
{{--                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));--}}
{{--            });--}}
{{--        </script>--}}
{{--    @endpush--}}
{{--@endonce--}}
