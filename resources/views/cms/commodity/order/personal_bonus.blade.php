@extends('layouts.main')
@section('sub-content')
    個人獎金

    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col">訂單編號</th>
                        <th scope="col">品名規格</th>
                        <th scope="col">金額</th>
                        <th scope="col">當代獎金</th>
                        <th scope="col">數量</th>
                        <th scope="col">小計</th>
                        <th scope="col">出庫數量</th>
                        <th scope="col">倉庫</th>
                        <th scope="col">產品人員</th>

                    </tr>
                </thead>
                <tbody>
                    @php
                        $total = 0;
                    @endphp
                    @foreach ($dataList as $key => $data)
                        @php
                            $total += $data->bonus;
                        @endphp
                        <tr>
                            <td>{{ $data->sub_order_sn }}</td>
                            <td>{{ $data->product_title }}</td>
                            <td>{{ $data->price }}</td>
                            <td>{{ $data->bonus }}</td>
                            <td>{{ $data->qty }}</td>
                            <td>{{ $data->origin_price }}</td>
                            <td>{{ $data->qty }}</td>
                            <td></td>
                            <td>
                                {{ $data->product_user }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div>
                總共:{{ $total }}元
            </div>
        </div>
    </div>
@endsection
