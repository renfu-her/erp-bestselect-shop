@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">退貨列表 {{ $delivery ? '('.$delivery->sn.')' : '' }}</h2>

    <div class="card shadow p-4 mb-4">
        @if(null != $delivery)
        <div class="col">
            <a href="{{ Route('cms.delivery.back_create', ['deliveryId' => $delivery->id], true) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg pe-1"></i> 新增退貨單
            </a>
        </div>
        @endif
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-1 small">
                <thead class="align-middle">
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <th scope="col" style="width:40px" class="text-center">明細</th>
                        <th scope="col" class="wrap lh-sm">
                            <div class="fw-bold">退貨單號</div>
                            @if(true == is_null($delivery))
                                <div>訂單編號</div>
                            @endif
                        </th>
                        <th scope="col">退貨入庫</th>
                        <th scope="col">退貨時間</th>
                        <th scope="col">退貨入庫時間</th>
                        <th scope="col">退貨備註</th>
                        <th scope="col" class="text-center">刪除</th>
                    </tr>
                </thead>
                <tbody>
                 @foreach ($dataList as $key =>$data)
                     <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td class="text-center">
                        {{-- <a href="{{ Route('cms.delivery.back_edit', ['bac_papa_id' => $data->id], true) }}"
                            data-bs-toggle="tooltip" title="編輯"
                            class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                            <i class="bi bi-pencil-square"></i>
                        </a> --}}
                            <a href="{{ Route('cms.delivery.back_detail', ['bac_papa_id' => $data->id], true) }}"
                            data-bs-toggle="tooltip" title="明細"
                            class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </td>
                        <td class="wrap">
                            <div class="fw-bold">{{$data->sn}}</div>
                            @if(true == is_null($delivery))
                                <div>{{$data->event_sn}}</div>
                            @endif
                        </td>
                        <td>
                            @if (isset($data->inbound_date))
                                <a class="btn btn-sm btn-danger" href="javascript:void(0)"
                                data-href="{{ Route('cms.delivery.back_inbound_delete', ['bac_papa_id' => $data->id], true) }}"
                                data-bs-toggle="modal" data-bs-target="#confirm-delete">
                                刪除退貨入庫</a>
                            @else
                                <a class="btn btn-sm btn-success"
                                href="{{ Route('cms.delivery.back_inbound', ['bac_papa_id' => $data->id], true) }}">退貨入庫審核</a>
                            @endif
                        </td>
                        <td class="wrap">{{date('Y/m/d H:i:s', strtotime($data->created_at))}}</td>
                        <td class="wrap">{{$data->inbound_date ? date('Y/m/d H:i:s', strtotime($data->inbound_date)): ''}}</td>
                        <td class="wrap">{{$data->memo}}</td>
                        <td class="text-center">
                            <a href="javascript:void(0)"
                            data-href="{{ Route('cms.delivery.back_delete', ['bac_papa_id' => $data->id], true) }}"
                            data-bs-toggle="modal" data-bs-target="#confirm-delete"
                            class="icon icon-btn fs-5 text-danger rounded-circle border-0">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                     </tr>
                 @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row flex-column-reverse flex-sm-row">
        <div class="col-auto">
            @if(null != $delivery)
                @if($delivery->event == App\Enums\Delivery\Event::order()->value)
                    <a href="{{ Route('cms.order.detail', ['id' => $order_id, 'subOrderId' => $eventId ]) }}" class="btn btn-outline-primary px-4" role="button">返回明細</a>
                @elseif($delivery->event == App\Enums\Delivery\Event::consignment()->value)
                    <a href="{{ Route('cms.consignment.edit', ['id' => $eventId ]) }}" class="btn btn-outline-primary px-4" role="button">返回明細</a>
                @elseif($delivery->event == App\Enums\Delivery\Event::csn_order()->value)
                    <a href="{{ Route('cms.consignment-order.edit', ['id' => $eventId ]) }}" class="btn btn-outline-primary px-4" role="button">返回明細</a>
                @endif
            @else

            @endif
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
