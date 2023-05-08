@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $purchaseData->purchase_sn }} {{ '採購單' }}</h2>

    <x-b-pch-navi :id="$id" :purchaseData="$purchaseData"></x-b-pch-navi>

    <div class="card shadow p-4 mb-4">
        <h6>退出列表</h6>

        <div class="col">
            <a href="{{ Route('cms.purchase.return_create', ['purchase_id' => $purchaseData->id]) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg pe-1"></i> 新增退出單
            </a>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-1 small">
                <thead class="align-middle">
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <th scope="col" style="width:40px" class="text-center">明細</th>
                        <th scope="col" class="wrap lh-sm">退出單號</th>
                        <th scope="col">退出入庫</th>
                        <th scope="col">退出時間</th>
                        <th scope="col">退出入庫時間</th>
                        <th scope="col">退出備註</th>
                        <th scope="col" class="text-center">刪除</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($data_list as $key => $data)
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td class="text-center">
                            <a href="{{ Route('cms.purchase.return_detail', ['return_id' => $data->id]) }}"
                            data-bs-toggle="tooltip" title="明細"
                            class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </td>
                        <td class="wrap">
                            <div class="fw-bold">{{ $data->sn }}</div>
                        </td>

                        <td>
                            @if (isset($data->inbound_date))
                                <a class="btn btn-sm btn-danger" href="javascript:void(0)"
                                data-href="{{ route('cms.purchase.return_inbound_delete', ['return_id' => $data->id]) }}"
                                data-bs-toggle="modal" data-bs-target="#confirm-delete">
                                刪除退出入庫</a>
                            @else
                                @if($data->return_main_item_num > 0)
                                <a class="btn btn-sm btn-success" href="{{ route('cms.purchase.return_inbound', ['return_id' => $data->id]) }}">退出入庫審核</a>
                                @endif
                            @endif
                        </td>

                        <td class="wrap">{{ date('Y/m/d H:i:s', strtotime($data->created_at)) }}</td>
                        <td class="wrap">{{ $data->inbound_date ? date('Y/m/d H:i:s', strtotime($data->inbound_date)) : '' }}</td>
                        <td class="wrap">{{ $data->memo }}</td>
                        <td class="text-center">
                            <a href="javascript:void(0)"
                            data-href="{{ $data->inbound_date ? '#' : route('cms.purchase.return_delete', ['return_id' => $data->id]) }}"
                            data-bs-toggle="modal" data-bs-target="#confirm-delete"
                            class="icon icon-btn fs-5 text-danger rounded-circle border-0 {{ $data->inbound_date ? 'disabled' : '' }}">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
