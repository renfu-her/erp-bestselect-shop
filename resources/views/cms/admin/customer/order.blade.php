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
                    <th scope="col">訂單編號</th>
                    <th scope="col" class="text-center">明細</th>
                    <th scope="col">訂單狀態</th>
                    <th scope="col">出貨單號</th>
                    <th scope="col">訂購日期</th>
                    <th scope="col">購買人</th>
                    <th scope="col">購買人電話</th>
                    <th scope="col">收件人姓名</th>
                    <th scope="col">收件人電話</th>
                    <th scope="col">銷售通路</th>
                    <th scope="col">物態</th>
                    <th scope="col">收款單號</th>
                    <th scope="col">物流型態</th>
                    <th scope="col">客戶物流方式</th>
                    <th scope="col">實際物流</th>
                    <th scope="col">包裹編號</th>
                    <th scope="col">退貨狀態</th>
                </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $key => $data)
                        @foreach($data['sub_order'] as $subOrderData)
                            <tr>
                                <td>{{ $subOrderData->sn }}</td>
                                <td class="text-center">
                                    <a href="{{ Route('cms.order.detail', ['id' => $data['order']->id]) }}"
                                       data-bs-toggle="tooltip"
                                       title="明細"
                                       class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-card-list"></i>
                                    </a>
                                </td>
                                <td>{{ $data['order']->status }}</td>
                                <td></td>
                                <td>{{ explode(' ', $data['order']->created_at)[0] }}</td>
                                <td>{{ $data['order']->ord_name }}</td>
                                <td>{{ $data['order']->ord_phone }}</td>
                                <td>{{ $data['order']->rec_name }}</td>
                                <td>{{ $data['order']->rec_phone }}</td>
                                <td>{{ $data['order']->sale_title }}</td>
                                <td class="text-success">{{ $subOrderData->logistic_status }}</td>
                                <td>{{ $subOrderData->received_sn }}</td>
                                <td>{{ $subOrderData->ship_category_name }}</td>
                                <td>{{ $subOrderData->ship_event }}</td>
                                <td>{{ $subOrderData->ship_group_name }}</td>
                                <td>{{ $subOrderData->package_sn }}</td>
                                <td>-</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
