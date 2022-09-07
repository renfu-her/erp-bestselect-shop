@extends('layouts.main')
@section('sub-content')
    <div>
        <x-b-customer-navi :customer="$customer"></x-b-customer-navi>
    </div>
    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                <tr>
                    <th scope="col" style="width:40px">#</th>
                    <th scope="col" style="width:40px" class="text-center">明細</th>
                    <th scope="col">訂單編號</th>
                    <th scope="col" class="wrap lh-sm">訂單狀態 /<br>物流狀態</th>
                    <th scope="col">出貨單號</th>
                    <th scope="col">訂購日期</th>
                    <th scope="col">購買人</th>
                    <th scope="col">購買人電話</th>
                    <th scope="col">銷售通路</th>
                    <th scope="col">收款單號</th>
                    <th scope="col">客戶物流</th>
                    <th scope="col">實際物流</th>
                    <th scope="col">包裹編號</th>
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
                            <td>{{ $data->order_sn }}</td>
                            <td class="wrap">
                                <div class="text-nowrap lh-sm @if ($data->order_status === '取消') text-danger @endif">
                                    {{ $data->order_status ?? '-' }} /</div>
                                <div class="text-nowrap lh-base">{{ $data->logistic_status }}</div>
                            </td>
                            <td>
                                @if ($data->projlgt_order_sn)
                                    <a href="{{ env('LOGISTIC_URL') . 'guest/order-flow/' . $data->projlgt_order_sn }}"
                                       target="_blank" class="btn btn-link">
                                        {{ $data->projlgt_order_sn }}
                                    </a>
                                @else
                                    {{ $data->package_sn }}
                                @endif
                            </td>
                            <td>{{ date('Y/m/d', strtotime($data->order_date)) }}</td>
                            <td>{{ $data->name }}</td>
                            <td>{{ $data->ord_phone }}</td>
                            <td>{{ $data->sale_title }}</td>
                            <td>{{ $data->or_sn }}</td>
                            <td class="wrap">
                                <div class="lh-1 small text-nowrap">
                                    <span>{{ $data->ship_category_name }}</span>
                                </div>
                                <div class="lh-base text-nowrap">{{ $data->ship_event }}</div>
                            </td>
                            <td>{{ $data->ship_group_name }}</td>
                            <td>{{ $data->package_sn }}</td>
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
