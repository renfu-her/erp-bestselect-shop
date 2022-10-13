@extends('layouts.main')
@section('sub-content')
    <div class="col-auto">
        @if (isset($prevPage))
            <a href="{{ $prevPage }}" class="btn btn-outline-primary px-4" role="button">
                返回上一頁
            </a>
        @endif
    </div>
    @if (isset($search))
        <h2 class="mb-4">商品銷售報表</h2>
        <x-b-report-search>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">人員</label>
                <select class="form-select -select2 -multiple" multiple name="user_id[]" aria-label="人員" data-placeholder="多選">
                    @foreach ($users as $value)
                        <option value="{{ $value->id }}" @if (in_array($value->id, $cond['user_id'])) selected @endif>
                            {{ $value->name }}</option>
                    @endforeach
                </select>
            </div>
        </x-b-report-search>

        @can('cms.user-performance-report.renew')
            <form id="form2" action="{{ route('cms.user-performance-report.renew') }}" method="POST">
                @csrf
                <div class="card shadow p-4 mb-4">
                    <div class="row">
                        <div class="col pe-0">
                            <select class="form-select" name="year" aria-label="年度">
                                <option value="" disabled>選擇年度</option>
                                @foreach ($year as $value)
                                    <option value="{{ $value }}" @if ($value == $cond['year']) selected @endif>
                                        {{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col pe-0">
                            <select class="form-select" name="month" aria-label="月份">
                                <option value="" disabled>選擇月份</option>
                                @for ($i = 1; $i < 13; $i++)
                                    <option value="{{ $i }}" @if ($i == $cond['month']) selected @endif>
                                        {{ $i }}月</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                立即統計
                                <div class="spinner-border spinner-border-sm" hidden role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @endcan
    @else
        <h2 class="mb-4">{{ $pageTitle }}</h2>
    @endif

    <div class="card shadow p-4 mb-4">
        <h4>
            {{ $pageTitle }}
        </h4>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="small">
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <th scope="col">
                            姓名
                        </th>
                        <th scope="col" class="text-center">線上營業額</th>
                        <th scope="col" class="text-center">線上毛利</th>
                        <th scope="col" class="text-center">線下營業額</th>
                        <th scope="col" class="text-center">線下毛利</th>
                        <th scope="col" class="text-center">總營業額</th>
                        <th scope="col" class="text-center">總毛利</th>
                        <!-- <th scope="col" class="text-center">人數</th>-->
                    </tr>
                </thead>
                <tbody>
                    @php
                        $on_price = 0;
                        $on_gross_profit = 0;
                        $off_price = 0;
                        $off_gross_profit = 0;
                        $total_gross_profit = 0;
                        $total_price = 0;
                        
                    @endphp
                    @foreach ($dataList as $key => $data)
                        @php
                            $on_price += $data->on_price;
                            $on_gross_profit += $data->on_gross_profit;
                            $off_price += $data->off_price;
                            $off_gross_profit += $data->off_gross_profit;
                            $total_gross_profit += $data->total_gross_profit;
                            $total_price += $data->total_price;
                            // $users += $data->users;
                          
                        @endphp
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td> <a
                                    href="{{ route('cms.product-manager-report.product', array_merge(['user_id' => $data->user_id],$query)) }}">
                                    {{ $data->name }}</a>
                            </td>
                            <td class="text-center">{{ $data->on_price }}</td>
                            <td class="text-center">{{ $data->on_gross_profit }}</td>
                            <td class="text-center">{{ $data->off_price }}</td>
                            <td class="text-center">{{ $data->off_gross_profit }}</td>
                            <td class="text-center">{{ $data->total_price }}</td>
                            <td class="text-center">{{ $data->total_gross_profit }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">合計</th>
                        <th class="text-center">{{ $on_price }}</th>
                        <th class="text-center">{{ $on_gross_profit }}</th>
                        <th class="text-center">{{ $off_price }}</th>
                        <th class="text-center">{{ $off_gross_profit }}</th>
                        <th class="text-center">{{ $total_price }}</th>
                        <th class="text-center">{{ $total_gross_profit }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
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
    @push('sub-scripts')
        <script></script>
    @endpush
@endOnce
