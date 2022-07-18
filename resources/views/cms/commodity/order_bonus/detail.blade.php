@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">分潤報表</h2>

    <table>
        <tr>
            <td>姓名
            </td>
            <td>{{ $report->name }}_{{ $report->mcode }}
            </td>
        </tr>
        <tr>
            <td>總金額</td>
            <td>{{ $report->bonus }}</td>
        </tr>
        <tr>
            <td>總筆數</td>
            <td>{{ $report->qty }}</td>
        </tr>
        <tr>
            <td>確認時間</td>
            <td>{{ $report->checked_at }}</td>
        </tr>

    </table>

    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox mb-3">
            <table class="table tableList table-striped mb-1">
                <thead>
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <th scope="col">子訂單</th>
                        <th scope="col">品名規格</th>
                        <th scope="col">出庫數量</th>
                        <th scope="col" class="text-center px-3">獎金</th>
                        <th scope="col" class="text-center px-3">出庫日</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $item)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $item->sub_order_sn }}</td>
                            <td>{{ $item->product_title }}</td>
                            <td>{{ $item->qty }}</td>
                            <td class="text-center">$ {{ number_format($item->bonus) }}</td>
                            <td class="text-center">$ {{ $item->dlv_audit_date }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
@endsection
@once
    @push('sub-scripts')
    @endpush
@endOnce
