@extends('layouts.main')
@section('sub-content')
    <div>
        <x-b-customer-navi :customer="$customer"></x-b-customer-navi>
    </div>
    <div class="card shadow p-4 mb-4">
        <h6>獲得紀錄</h6>
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="small">
                    <tr>
                        <th scope="col">訂單編號</th>
                        <th scope="col">訂單日期</th>
                        <th scope="col">獲得點數</th>
                        <th scope="col">使用期限</th>
                        <th scope="col">來源類型</th>
                        <th scope="col">行為</th>
                        <th scope="col">備註</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        use App\Enums\Discount\DividendCategory;
                        $getTotal = 0;
                    @endphp
                    @foreach ($get_record as $key => $data)
                        @php
                            $getTotal += $data->dividend;
                        @endphp
                        <tr>
                            <td>{{ $data->category_sn }}</td>
                            <td class="wrap">
                                @if ($data->category !== DividendCategory::Cyberbiz)
                                    {{ date('Y/m/d H:i:s', strtotime($data->created_at)) }}
                                @endif
                            </td>
                            <td>{{ number_format($data->dividend) }}</td>
                            <td class="wrap">
                                {{ $data->active_edate ? date('Y/m/d H:i:s', strtotime($data->active_edate)) : '-' }}</td>
                            <td>{{ DividendCategory::getDescription($data->category) }}</td>
                            <td class="wrap">{{ $data->flag_title }}</td>
                            <td class="wrap">{{ $data->note }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td></td>
                        <td></td>
                        <td>總計：{{ number_format($getTotal) }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card shadow p-4 mb-4">
        <h6>使用紀錄</h6>
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="small">
                    <tr>
                        <th scope="col">訂單編號</th>
                        <th scope="col">訂單日期</th>
                        <th scope="col">使用點數</th>
                        <th scope="col">行為</th>
                        <th scope="col">備註</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $usedTotal = 0;
                    @endphp
                    @foreach ($use_record as $key => $data)
                        @php
                            $usedTotal += $data->dividend;
                        @endphp
                        <tr>
                            <td>{{ $data->category_sn }}</td>
                            <td>{{ date('Y/m/d H:i:s', strtotime($data->created_at)) }}</td>
                            <td>{{ $data->dividend }}</td>
                            <td class="wrap">{{ $data->flag_title }}</td>
                            <td class="wrap">{{ $data->note }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td></td>
                        <td></td>
                        <td>總計：{{ $usedTotal }}</td>
                        <td></td>
                        <td></td>

                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card shadow p-4 mb-4">
        <h6>過期點數</h6>
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="small">
                    <tr>
                        <th scope="col">原因</th>
                        <th scope="col">訂單日期</th>
                        <th scope="col">點數</th>
                        <th scope="col">行為</th>
                        <th scope="col">備註</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $expiredTotal = 0;
                    @endphp
                    @foreach ($expired as $key => $data)
                        @php
                            $expiredTotal += $data->dividend;
                        @endphp
                        <tr>
                            <td>{{ $data->note }}</td>
                            <td>{{ date('Y/m/d H:i:s', strtotime($data->created_at)) }}</td>
                            <td>{{ $data->dividend }}</td>
                            <td class="wrap">{{ $data->flag_title }}</td>
                            <td class="wrap">{{ $data->note }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td></td>
                        <td></td>
                        <td>總計：{{ $expiredTotal }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <tr>
                    <td>剩餘點數: {{ $getTotal + $usedTotal + $expiredTotal }}</td>
                </tr>
            </table>
            <table class="table table-striped tableList">
                <thead class="small">
                    <tr>
                        <th scope="col">類型</th>
                        <th scope="col">點數</th>
                        <th scope="col">截止日</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($remain as $key => $data)
                        <tr>
                            <td>{{ $data['category_ch'] }}</td>
                            <td>{{ $data['dividend'] }}</td>
                            <td>{{ $data['active_edate'] }}</td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
@endsection
