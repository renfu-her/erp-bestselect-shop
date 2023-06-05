@php use Illuminate\Support\Facades\Auth; @endphp
@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">手動發放紅利</h2>
    <div class="card shadow p-4 mb-4">
        <div class="row mb-4">
            <div class="col">
                <a href="{{ Route('cms.manual-dividend.create', null, true) }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> 新增
                </a>
                <a href="{{ Route('cms.manual-dividend.sample', null, true) }}" class="btn btn-primary">
                    範本
                </a>
            </div>

        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">詳細</th>
                        <th scope="col">類別</th>
                        <th scope="col">建立者</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList ?? [] as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>
                                <a href="{{ Route('cms.manual-dividend.show', ['id' => $data->id], true) }}">
                                    詳細
                                </a>
                            </td>
                            <td>{{ $data->category_title }}</td>
                            <td>{{ $data->user_name }}</td>

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

            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
        </script>
    @endpush
@endonce
