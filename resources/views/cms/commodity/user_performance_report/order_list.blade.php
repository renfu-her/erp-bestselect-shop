@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">{{ $pageTitle }}</h2>
    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-0">
                <thead class="small">
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <th scope="col"> 訂單號碼 </th>
                        <th scope="col"> 銷售類型 </th>
                        <th scope="col" class="text-end">營業額</th>
                        <th scope="col" class="text-end">毛利</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $origin_price = 0;
                        $gross_profit = 0;
                    @endphp
                    @foreach ($dataList as $key => $data)
                        @php
                            $origin_price += $data->origin_price;
                            $gross_profit += $data->gross_profit;
                        @endphp
                        <tr @class(['table-success' => $data->sales_type == '1', 'table-warning' => $data->sales_type == '0'])>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>
                                <a href="{{ route('cms.order.detail', ['id' => $data->id]) }}">
                                    {{ $data->sn }}
                                </a>
                            </td>
                            <td>
                                @if ($data->sales_type == '1')
                                    線上
                                @else
                                    線下
                                @endif
                            </td>
                            <td class="text-end">
                                <x-b-number :val="$data->origin_price" prefix="$" />
                            </td>
                            <td class="text-end">
                                <x-b-number :val="$data->gross_profit" prefix="$" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">合計</th>
                        <th></th>
                        <th class="text-end">
                            <x-b-number :val="$origin_price" prefix="$" />
                        </th>
                        <th class="text-end">
                            <x-b-number :val="$gross_profit" prefix="$" />
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="col-auto">
        @if (isset($prevPage))
            <a href="{{ $prevPage }}" class="btn btn-outline-primary px-4" role="button">
                返回上一頁
            </a>
        @endif
    </div>
@endsection
@once
    @push('sub-styles')
        <style>
            h4 {
                color: #415583;
            }
        </style>
    @endpush
@endOnce
