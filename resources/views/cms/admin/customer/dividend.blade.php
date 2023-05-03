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
                    </tr>
                </thead>
                <tbody>
                @php
                    use App\Enums\Discount\DividendCategory;
                @endphp
                @foreach ($get_record as $key => $data)
                    <tr>
                        <td>{{ $data->category_sn }}</td>
                        <td class="wrap">
                            @if($data->category !== DividendCategory::Cyberbiz)
                                {{ date('Y/m/d H:i:s', strtotime($data->created_at)) }}
                            @endif
                        </td>
                        <td>{{ number_format($data->dividend) }}</td>
                        <td class="wrap">{{ date('Y/m/d H:i:s', strtotime($data->active_edate)) }}</td>
                        <td>{{ DividendCategory::getDescription($data->category) }}</td>
                    </tr>
                @endforeach
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
                    </tr>
                </thead>
                <tbody>
                @foreach ($use_record as $key => $data)
                    <tr>
                        <td>{{ $data->category_sn }}</td>
                        <td>{{ date('Y/m/d H:i:s', strtotime($data->created_at)) }}</td>
                        <td>{{ $data->dividend }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
