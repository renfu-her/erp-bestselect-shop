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
        <h2 class="mb-4">採購營收報表</h2>
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
    @else
        <h2 class="mb-4">{{ $pageTitle }}</h2>
    @endif

    <div class="card shadow p-4 mb-4">
        @if (isset($search))
            @can('cms.product-manager-report.renew')
                <form id="form2" action="{{ route('cms.product-manager-report.renew') }}" method="POST">
                    @csrf
                    <div class="d-flex justify-content-end align-items-center mb-3 flex-wrap">
                        <span class="text-muted me-1">重新計算</span>
                        <div class="col-auto me-1">
                            <select class="form-select form-select-sm" name="year" aria-label="年度">
                                <option value="" disabled>選擇年度</option>
                                @foreach ($year as $value)
                                    <option value="{{ $value }}" @if ($value == $cond['year']) selected @endif>
                                        {{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto me-1">
                            <select class="form-select form-select-sm" name="month" aria-label="月份">
                                <option value="" disabled>選擇月份</option>
                                @for ($i = 1; $i < 13; $i++)
                                    <option value="{{ $i }}" @if ($i == $cond['month']) selected @endif>
                                        {{ $i }}月</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">
                                立即統計
                                <div class="spinner-border spinner-border-sm" hidden role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </form>
            @endcan
            <h4>{{ $pageTitle }}</h4>
        @endif

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="small align-middle">
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <th scope="col">姓名</th>
                        <th scope="col" class="text-center lh-1 table-success">線上<br class="d-block d-xl-none">營業額</th>
                        <th scope="col" class="text-center lh-1 table-success">線上<br class="d-block d-xl-none">毛利</th>
                        <th scope="col" class="text-center lh-1 table-warning">線下<br class="d-block d-xl-none">營業額</th>
                        <th scope="col" class="text-center lh-1 table-warning">線下<br class="d-block d-xl-none">毛利</th>
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
                            unset($query['user_id']);
                        @endphp
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td> <a
                                    href="{{ route('cms.product-manager-report.product', array_merge(['user_id' => $data->user_id],$query)) }}">
                                    {{ $data->name }}</a>
                            </td>
                            <td class="text-end table-success">
                                <x-b-number :val="$data->on_price" prefix="$" />
                            </td>
                            <td class="text-end table-success">
                                <x-b-number :val="$data->on_gross_profit" prefix="$" />
                            </td>
                            <td class="text-end table-warning">
                                <x-b-number :val="$data->off_price" prefix="$" />
                            </td>
                            <td class="text-end table-warning">
                                <x-b-number :val="$data->off_gross_profit" prefix="$" />
                            </td>
                            <td class="text-end">
                                <x-b-number :val="$data->total_price" prefix="$" />
                            </td>
                            <td class="text-end">
                                <x-b-number :val="$data->total_gross_profit" prefix="$" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">合計</th>
                        <th class="text-end table-success">
                            <x-b-number :val="$on_price" prefix="$" />
                        </th>
                        <th class="text-end table-success">
                            <x-b-number :val="$on_gross_profit" prefix="$" />
                        </th>
                        <th class="text-end table-warning">
                            <x-b-number :val="$off_price" prefix="$" />
                        </th>
                        <th class="text-end table-warning">
                            <x-b-number :val="$off_gross_profit" prefix="$" />
                        </th>
                        <th class="text-end">
                            <x-b-number :val="$total_price" prefix="$" />
                        </th>
                        <th class="text-end">
                            <x-b-number :val="$total_gross_profit" prefix="$" />
                        </th>
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
@endOnce
