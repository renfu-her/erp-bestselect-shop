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
                    <th scope="col" style="width:10%">#</th>
                    <th scope="col">名稱</th>
                    <th scope="col">優惠金額或%數</th>
                    <th scope="col">序號</th>
                    <th scope="col">使用方式</th>
                    <th scope="col">使用期限</th>
                    <th scope="col">使用狀況</th>
                    <th scope="col">使用範圍</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($dataList as $key => $data)
                    @php
                        $status = '未啟用';
                        if (isset($data->active_sdate) && !empty($data->active_sdate)) {
                            $status = '進行中';
                            $active_edate = date('Y-m-d H:m:s', strtotime($data->active_edate));
                            $today = date('Y-m-d H:m:s');
                            if (isset($data->active_edate) && !empty($data->active_edate) && $active_edate <= $today) {
                                $status = '已過期';
                            } else {
                                if ($data->used) {
                                    $status = '已使用';
                                }
                            }
                        }
                    @endphp
                    @php
                        $discount_value_title = '';
                        if(\App\Enums\Discount\DisMethod::cash()->value == $data->method_code) {
                            $discount_value_title = '$'. $data->discount_value;
                        } elseif(\App\Enums\Discount\DisMethod::percent()->value == $data->method_code) {
                            $discount_value_title = $data->discount_value. '%';
                        }
                    @endphp
                    @php
                        //使用方式
                        $note = '';
                        // 使用優惠券
                        if('code' == $data->category_code) {
                            $note = $note. '【$'. $data.title. '】';
                        }
                        //低消
                        if(0 < $data->min_consume) {
                            $note = $note. '消費滿 $'. $data->min_consume;
                        } else {
                            $note = $note. '消費不限金額';
                        }
                        switch ($data->method_code) {
                            case 'cash':
                                $note = $note. '折 $'. $data->discount_value. '（不得折抵運費）';
                                break;
                            case 'percent':
                                $note = $note. '享 '. $data->discount_value. '折優惠（不含運費）';
                                break;
                            case 'coupon':
                                $note = $note. '送優惠券';
                                break;
                            default:
                                $note = $note. '享優惠';
                        }
                    @endphp
                    @php
                    //使用範圍
                    $range = '適用所有商品';
                    if (0 == $data->is_global) {
                        $range = '查看適用商品';
                    }
                    @endphp
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $data->title }}</td>
                        <td>{{ $discount_value_title }}</td>
                        <td>{{ $data->sn }}</td>
                        <td>{{ $note }}</td>
                        <td>@if(!empty($data->active_sdate)) {{date('Y-m-d', strtotime($data->active_sdate))}} ~ {{date('Y-m-d', strtotime($data->active_edate))}} @else 未啟用 @endif</td>
                        <td>{{ $status }}</td>
                        <td>@if(0 == $data->is_global)
                                <a href="{{env('FRONTEND_URL'). 'collection/'. $data->collection_ids }}" class="btn btn-link">查看適用商品</a>
                            @else 適用所有商品 @endif</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
@once
    @push('sub-scripts')
        <script>
            // 依使用期限排序
            $('#order').click(function(){
                $('#search').submit();
            });

        </script>
    @endpush
@endonce

