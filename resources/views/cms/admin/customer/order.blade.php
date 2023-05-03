@extends('layouts.main')
@section('sub-content')
    <div>
        <x-b-customer-navi :customer="$customer"></x-b-customer-navi>
    </div>
    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList small">
                <thead class="align-middle">
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <th scope="col" style="width:40px" class="text-center">明細</th>
                        <th scope="col">訂單編號</th>
                        <th scope="col" class="wrap">
                            <span class="text-nowrap">訂單狀態</span>
                            <span class="text-nowrap">物流狀態</span>
                        </th>
                        <th scope="col" class="wrap text-center">
                            <span class="text-nowrap">出貨單號</span>
                            <span class="text-nowrap">收款單號</span>
                        </th>
                        <th scope="col">訂購日期</th>
                        <th scope="col" class="wrap">
                            <div class="text-nowrap">購買人</div>
                            <div class="text-nowrap">電話</div>
                        </th>
                        <th scope="col">銷售通路</th>
                        <th scope="col">客戶物流</th>
                        <th scope="col" class="wrap">
                            <span class="text-nowrap">實際物流</span>
                            <span class="text-nowrap">包裹編號</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td class="text-center">
                                @can('cms.order.detail')
                                    <a href="{{ Route('cms.order.detail', ['id' => $data->id]) }}" data-bs-toggle="tooltip"
                                       title="明細" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-card-list"></i>
                                    </a>
                                @endcan
                            </td>
                            <td class="wrap">{{ $data->order_sn }}</td>
                            <td class="wrap">
                                <div class="text-nowrap @if ($data->order_status === '取消') text-danger @endif">
                                    {{ $data->order_status ?? '-' }}</div>
                                <div class="text-nowrap">{{ $data->logistic_status }}</div>
                            </td>
                            <td class="wrap text-center">
                                <div class="text-nowrap">
                                @if ($data->projlgt_order_sn)
                                    <a href="{{ env('LOGISTIC_URL') . 'guest/order-flow/' . $data->projlgt_order_sn }}"
                                       target="_blank">
                                        {{ $data->projlgt_order_sn }}
                                    </a>
                                @else
                                    {{ $data->package_sn ?? '-' }}
                                @endif
                                </div>
                                <div class="text-nowrap">{{ $data->or_sn }}</div>
                            </td>
                            <td>{{ date('Y/m/d', strtotime($data->order_date)) }}</td>
                            <td class="wrap">
                                <div class="text-nowrap">{{ $data->name }}</div>
                                <div class="text-nowrap">{{ $data->ord_phone }}</div>
                            </td>
                            <td class="wrap">{{ $data->sale_title }}</td>
                            <td class="wrap">
                                <div class="lh-1 text-nowrap">
                                    <span @class([
                                        'badge -badge',
                                        '-primary' => $data->ship_category_name === '宅配',
                                        '-warning' => $data->ship_category_name === '自取',
                                    ])>{{ $data->ship_category_name }}</span>
                                </div>
                                <div class="lh-base">{{ $data->ship_event }}</div>
                            </td>
                            <td class="wrap">
                                <div class="text-nowrap">{{ $data->ship_group_name }}</div>
                                <div class="text-nowrap">{{ $data->package_sn }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            @if ($dataList)
                <div class="mx-3">共 {{ $dataList->lastPage() }} 頁(共找到 {{ $dataList->total() }} 筆資料)</div>
                {{-- 頁碼 --}}
                <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
            @endif
        </div>
    </div>
@endsection
@push('sub-styles')
    <style>
        .badge.-badge {
            color: #484848;
        }

        .badge.-badge.-primary {
            background-color: #cfe2ff;
        }

        .badge.-badge.-warning {
            background-color: #fff3cd;
        }
    </style>
@endpush
