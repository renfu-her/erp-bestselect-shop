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

                </tbody>
            </table>
        </div>
    </div>
@endsection
