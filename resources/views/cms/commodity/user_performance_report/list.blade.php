@extends('layouts.main')
@section('sub-content')

    @if (isset($search))
        <h2 class="mb-4">業績報表</h2>
        <x-b-report-search>
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
        </x-b-report-search>

    @else
        <h2 class="mb-4">{{ $pageTitle }}</h2>
    @endif

    <div class="card shadow p-4 mb-4">
        @if (isset($search))
            @can('cms.user-performance-report.renew')
                <form id="form2" action="{{ route('cms.user-performance-report.renew') }}" method="POST">
                    @csrf
                    <div class="d-flex justify-content-end align-items-center mb-3">
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
                        <th scope="col">
                            @if ($targetType != 'user')
                                部門名稱
                            @else
                                姓名
                            @endif
                        </th>
                        <th scope="col" class="text-center lh-1 table-success">線上<br class="d-block d-xl-none">營業額</th>
                        <th scope="col" class="text-center lh-1 table-success">線上<br class="d-block d-xl-none">毛利</th>
                        <th scope="col" class="text-center lh-1 table-warning">線下<br class="d-block d-xl-none">營業額</th>
                        <th scope="col" class="text-center lh-1 table-warning">線下<br class="d-block d-xl-none">毛利</th>
                        <th scope="col" class="text-center">總營業額</th>
                        <th scope="col" class="text-center">總毛利</th>
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
            $('#form2').submit(function(e) {
                $('#form2 .spinner-border').prop('hidden', false);
            });
        </script>
    @endpush
@endOnce
