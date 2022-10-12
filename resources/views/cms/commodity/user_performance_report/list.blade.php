@extends('layouts.main')
@section('sub-content')
    @if (isset($search))
        <h2 class="mb-4">業績報表</h2>
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
                                            value="{{ $key }}">
                                        {{ $value }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </fieldset>
                    <div class="col-12 col-sm-6 mb-3 -year">
                        <label class="form-label">年度</label>
                        <select class="form-select" name="year" aria-label="年度">
                            @foreach ($year as $value)
                                <option value="{{ $value }}" @if ($value == $cond['year']) selected @endif>
                                    {{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 mb-3 -season" hidden>
                        <label class="form-label">季</label>
                        <select class="form-select" name="season" aria-label="季">
                            @foreach ($season as $key => $value)
                                <option value="{{ $key }}" >
                                    第{{ $value }}季</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 mb-3 -month" hidden>
                        <label class="form-label">月份</label>
                        <select class="form-select" name="month" aria-label="月份">
                            @for ($i = 1; $i < 13; $i++)
                                <option value="{{ $i }}" @if ($i == $cond['month']) selected @endif>
                                    {{ $i }}月</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label">起訖日期</label>
                        <div class="input-group has-validation">
                            <input type="date" class="form-control @error('sDate') is-invalid @enderror"
                                   name="sDate" value="{{ $cond['sDate'] }}" aria-label="起始日期" />
                            <input type="date" class="form-control @error('eDate') is-invalid @enderror"
                                   name="eDate" value="{{ $cond['eDate'] }}" aria-label="結束日期" />
                            <div class="invalid-feedback">
                                @error('sDate')
                                {{ $message }}
                                @enderror
                                @error('eDate')
                                {{ $message }}
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <button type="submit" class="btn btn-primary px-4">搜尋</button>
                </div>
            </div>
        </form>

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
        @if (isset($search))
            <h4>{{ $pageTitle }}</h4>
        @endif

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-0">
                <thead class="small">
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <th scope="col">
                            @if ($targetType != 'user')
                                部門名稱
                            @else
                                姓名
                            @endif
                        </th>
                        <th scope="col" class="text-end table-success">線上營業額</th>
                        <th scope="col" class="text-end table-success">線上毛利</th>
                        <th scope="col" class="text-end table-warning">線下營業額</th>
                        <th scope="col" class="text-end table-warning">線下毛利</th>
                        <th scope="col" class="text-end">總營業額</th>
                        <th scope="col" class="text-end">總毛利</th>
                        <!-- <th scope="col" class="text-end">人數</th>-->
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
                            <td>
                                @switch($targetType)
                                    @case('department')
                                        <a
                                            href="{{ route('cms.user-performance-report.department', array_merge($query, ['organize_id' => $data->id])) }}">{{ $data->title }}</a>
                                    @break

                                    @case('group')
                                        <a
                                            href="{{ route('cms.user-performance-report.group', array_merge($query, ['organize_id' => $data->id])) }}">{{ $data->title }}</a>
                                    @break

                                    @case('user')
                                        <a
                                            href="{{ route('cms.user-performance-report.user', array_merge($query, ['user_id' => $data->id])) }}">{{ $data->title }}</a>
                                    @break

                                    @default
                                        {{ $data->title }}
                                @endswitch

                            </td>
                            <td @class([
                                'text-end table-success',
                                'text-danger fw-bold negative' => $data->on_price < 0,
                            ])>${{ number_format(abs($data->on_price)) }}</td>
                            <td @class([
                                'text-end table-success',
                                'text-danger fw-bold negative' => $data->on_gross_profit < 0,
                            ])>${{ number_format(abs($data->on_gross_profit)) }}</td>
                            <td @class([
                                'text-end table-warning',
                                'text-danger fw-bold negative' => $data->off_price < 0,
                            ])>${{ number_format(abs($data->off_price)) }}</td>
                            <td @class([
                                'text-end table-warning',
                                'text-danger fw-bold negative' => $data->off_gross_profit < 0,
                            ])>${{ number_format(abs($data->off_gross_profit)) }}</td>
                            <td @class([
                                'text-end',
                                'text-danger fw-bold negative' => $data->total_price < 0,
                            ])>${{ number_format(abs($data->total_price)) }}</td>
                            <td @class([
                                'text-end',
                                'text-danger fw-bold negative' => $data->total_gross_profit < 0,
                            ])>${{ number_format(abs($data->total_gross_profit)) }}</td>


                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">合計</th>
                        <th @class([
                            'text-end table-success',
                            'text-danger fw-bold negative' => $on_price < 0,
                        ])>${{ number_format(abs($on_price)) }}</th>
                        <th @class([
                            'text-end table-success',
                            'text-danger fw-bold negative' => $on_gross_profit < 0,
                        ])>${{ number_format(abs($on_gross_profit)) }}</th>
                        <th @class([
                            'text-end table-warning',
                            'text-danger fw-bold negative' => $off_price < 0,
                        ])>${{ number_format(abs($off_price)) }}</th>
                        <th @class([
                            'text-end table-warning',
                            'text-danger fw-bold negative' => $off_gross_profit < 0,
                        ])>${{ number_format(abs($off_gross_profit)) }}</th>
                        <th @class([
                            'text-end',
                            'text-danger fw-bold negative' => $total_price < 0,
                        ])>${{ number_format(abs($total_price)) }}</th>
                        <th @class([
                            'text-end',
                            'text-danger fw-bold negative' => $total_gross_profit < 0,
                        ])>${{ number_format(abs($total_gross_profit)) }}</th>
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

            .negative::before {
                content: '-';
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            // 立即統計
            $('#form2').submit(function (e) { 
                $('#form2 .spinner-border').prop('hidden', false);
            });

            // 搜尋條件
            $('input[name="type"][type="radio"]').on('change', function(e) {
                const val = $(this).val();
                switch (val) {
                    case 'year': // 年度
                        $('div.-season, div.-month').prop('hidden', true);
                        break;
                    case 'season': // 季
                        $('div.-month').prop('hidden', true);
                        $('div.-season').prop('hidden', false);
                        break;
                    case 'month': // 月份
                        $('div.-season').prop('hidden', true);
                        $('div.-month').prop('hidden', false);
                        break;

                    default:
                        break;
                }
                setDate(val);
            });
            $('#search select[name="year"], #search select[name="season"], #search select[name="month"]')
            .on('change', function(e) {
                const type = this.name;
                setDate(type);
            });

            // set 起訖日
           // setDate($('input[name="type"][type="radio"]:checked').val());
            function setDate(type) {
                const sDate = $('input[name="sDate"]');
                const eDate = $('input[name="eDate"]');
                let sdate = moment();
                let edate = moment();

                const Year = $('#search select[name="year"]').val();
                switch (type) {
                    case 'year':    // 年度
                        sdate = moment().year(Year).startOf('year');
                        edate = moment().year(Year).endOf('year');
                        break;
                    case 'season':  // 季
                        const Season = $('#search select[name="season"]').val();
                        sdate = moment().quarter(Season).startOf('quarter');
                        edate = moment().quarter(Season).endOf('quarter');
                        break;
                    case 'month':   // 月份
                        const Month = $('#search select[name="month"]').val();
                        sdate = moment().month(Month - 1).startOf('month');
                        edate = moment().month(Month - 1).endOf('month');
                        break;
                
                    default:
                        break;
                }

                sDate.val(sdate.format('YYYY-MM-DD'));
                eDate.val(edate.format('YYYY-MM-DD'));
            }
        </script>
    @endpush
@endOnce
