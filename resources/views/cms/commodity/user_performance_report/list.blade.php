@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">業績報表</h2>
    @if (isset($search))
        <form id="search" action="" method="GET">
            <div class="card shadow p-4 mb-4">
                <h6>搜尋條件</h6>
                <div class="row">
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">部門</label>
                        <select class="form-select -select2 -multiple" multiple name="department[]" aria-label="部門"
                            data-placeholder="多選">
                            @foreach ($department as $value)
                                <option value="{{ $value->id }}" @if (in_array($value->id, $cond['department'])) selected @endif>
                                    {{ $value->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <fieldset class="col-12 col-sm-6 mb-3">
                        <legend class="col-form-label p-0 mb-2">期間</legend>
                        <div class="px-1 pt-1">
                            @foreach ($type as $key => $value)
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" name="type" type="radio"
                                            value="{{ $key }}" @if ($key === $cond['type']) checked @endif>
                                        {{ $value }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </fieldset>
                    <div class="col-12 col-sm-6 mb-3 -year">
                        <label class="form-label">年度</label>
                        <select class="form-select -select" name="year" aria-label="年度">
                            @foreach ($year as $value)
                                <option value="{{ $value }}" @if ($value === $cond['year']) selected @endif>
                                    {{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 mb-3 -season" @if ($cond['type'] !== 'season') hidden @endif >
                        <label class="form-label">季</label>
                        <select class="form-select -select" name="season" aria-label="季">
                            @foreach ($season as $key => $value)
                                <option value="{{ $key }}" @if ($key === $cond['season']) selected @endif>
                                    第{{ $value }}季</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 mb-3 -month"@if ($cond['type'] !== 'month') hidden @endif >
                        <label class="form-label">月份</label>
                        <select class="form-select -select" name="month" aria-label="月份">
                            @for ($i = 1; $i < 13; $i++)
                                <option value="{{ $i }}" @if ($i === $cond['month']) selected @endif>
                                    {{ $i }}月</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="col">
                    <button type="submit" class="btn btn-primary px-4">搜尋</button>
                </div>
            </div>
        </form>
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
                        <th scope="col">部門名稱</th>
                        <th scope="col" class="text-center">線上營業額</th>
                        <th scope="col" class="text-center">線上毛利</th>
                        <th scope="col" class="text-center">線下營業額</th>
                        <th scope="col" class="text-center">線下毛利</th>
                        <th scope="col" class="text-center">總營業額</th>
                        <th scope="col" class="text-center">總毛利</th>
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
                        @endphp
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>
                                @switch($targetType)
                                    @case('department')
                                        <a
                                            href="{{ route('cms.user-performance-report.department', array_merge($query, ['organize_id' => $data->id])) }}">{{ $data->title }}</a>
                                    @break
                                    @default
                                        {{ $data->title }}
                                @endswitch

                            </td>
                            <td class="text-center">{{ $data->on_price }}</td>
                            <td class="text-center">{{ $data->on_gross_profit }}</td>
                            <td class="text-center">{{ $data->off_price }}</td>
                            <td class="text-center">{{ $data->off_gross_profit }}</td>
                            <td class="text-center">{{ $data->total_gross_profit }}</td>
                            <td class="text-center">{{ $data->total_price }}</td>
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
                        <th class="text-center">{{ $total_gross_profit }}</th>
                        <th class="text-center">{{ $total_price }}</th>
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
        <script>
            $('input[name="type"][type="radio"]').on('change', function(e) {
                const val = $(this).val();
                switch (val) {
                    case 'year':    // 年度
                        $('div.-season, div.-month').prop('hidden', true);
                        break;
                    case 'season':    // 季
                        $('div.-month').prop('hidden', true);
                        $('div.-season').prop('hidden', false);
                        break;
                    case 'month':    // 月份
                        $('div.-season').prop('hidden', true);
                        $('div.-month').prop('hidden', false);
                        break;
                
                    default:
                        break;
                }
            });
        </script>
    @endpush
@endOnce
