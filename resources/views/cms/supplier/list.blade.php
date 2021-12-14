@extends('layouts.main')
@section('sub-content')
<h2 class="mb-4">廠商管理</h2>
<div class="card shadow p-4 mb-4">
    <div class="row mb-4">
        <div class="col-auto">
            @can('cms.supplier.create')
            <a href="{{ Route('cms.supplier.create', null, true) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> 新增廠商
            </a>
            @endcan
        </div>
    </div>

    <div class="table-responsive tableOverBox">
        <table class="table table-striped tableList mb-0">
            <thead>
                <tr>
                    <th scope="col" style="width:10%">#</th>
                    <th scope="col">廠商名稱</th>
                    <th scope="col">廠商簡稱</th>
                    <th scope="col">統編</th>
                    <th scope="col">負責人</th>
                    <th scope="col">匯款銀行</th>
                    <th scope="col">匯款銀行代碼</th>
                    <th scope="col">匯款戶名</th>
                    <th scope="col">匯款帳號</th>
                    <th scope="col">聯絡電話</th>
                    <th scope="col">聯絡地址</th>
                    <th scope="col">聯絡人</th>
                    <th scope="col">電子郵件</th>
                    <th scope="col">備註</th>
                    <th scope="col" class="text-center">編輯</th>
                    <th scope="col" class="text-center">刪除</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dataList as $key => $data)
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $data->name }}</td>
                        <td>{{ $data->nickname }}</td>
                        <td>{{ $data->vat_no }}</td>
                        <td>{{ $data->chargeman }}</td>
                        <td>{{ $data->bank_cname }}</td>
                        <td>{{ $data->bank_code }}</td>
                        <td>{{ $data->bank_acount }}</td>
                        <td>{{ $data->bank_numer }}</td>
                        <td>{{ $data->contact_tel }}</td>
                        <td>{{ $data->contact_address }}</td>
                        <td>{{ $data->contact_person }}</td>
                        <td>{{ $data->email }}</td>
                        <td>{{ $data->memo }}</td>
                        <td class="text-center">
{{--                            @can('admin.supplier.edit')--}}
                            <a href="{{ Route('cms.supplier.edit', ['id' => $data->id], true) }}"
                                data-bs-toggle="tooltip" title="編輯"
                                class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                <i class="bi bi-pencil-square"></i>
                            </a>
{{--                            @endcan--}}
                        </td>
                        <td class="text-center">
{{--                            @can('admin.supplier.delete')--}}
                            <a href="javascript:void(0)" data-href="{{ Route('cms.supplier.delete', ['id' => $data->id], true) }}"
                                data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                <i class="bi bi-trash"></i>
                            </a>
{{--                            @endcan--}}
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
    @push('scripts')
        <script>
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
        </script>
    @endpush
@endonce
