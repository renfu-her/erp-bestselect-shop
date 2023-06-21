@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">季報表</h2>

    <div class="card shadow p-4 mb-4">
        <form method="GET">
            <div class="row align-items-center">
                <div class="col-auto">
                    <select class="form-select" name="y" aria-label="年度" placeholder="請選擇年度">
                        @foreach ($year_range as $value)
                            <option value="{{ $value }}" {{ $value == $cond['year'] ? 'selected' : '' }}>
                                {{ $value }}年</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto">
                    <select class="form-select" name="quarter" aria-label="季" data-placeholder="季度">
                        @for ($i = 1; $i <= 4; $i++)
                            <option value="{{ $i }}" {{ $i == $cond['quarter'] ? 'selected' : '' }}>
                                {{ $i }}季</option>
                        @endfor
                    </select>
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary px-4">查詢</button>
                </div>
                <div class="col-auto border-bottom border-success p-0 m-2">
                    <a href="{{ route('cms.user-performance-report.index') }}" target="_blank" class="text-success">
                        重新統計 <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
    
    <div class="card shadow p-4 mb-4">
        <div>
            <table class="table table-borderless">
                <tr>
                    <th>總商品數：{{ number_format($products) }}</th>
                    <th>總廠商數：{{ number_format($suppliers) }}</th>
                </tr>
            </table>
        </div>
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="align-middle">
                    <tr>
                        <th scope="col">月份</th>
                        <th scope="col" class="text-end">總營業額</th>
                        <th scope="col" class="text-end">總毛利</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total_gross_profit = 0;
                        $total_price = 0;
                        
                    @endphp
                    @foreach ($dataList as $key => $data)
                        @php

                            $total_gross_profit += $data->total_gross_profit;
                            $total_price += $data->total_price;
                          
                        @endphp
                        <tr>
                            <td> 
                                {{ $data->m }}月
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
                        <th>合計</th>
                       
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

    <ul class="nav nav-tabs border-bottom-0">
        <li class="nav-item">
            <button class="nav-link active" type="button" data-page="detail" aria-current="page">
                類別排名
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" type="button" data-page="chart">
                Chart
            </button>
        </li>
    </ul>

    <div id="-detail" class="card shadow p-4 mb-4 -page">
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="align-middle">
                    <tr>
                        <th scope="col" style="width: 10px">#</th>
                        <th scope="col">類別</th>
                        {{-- <th scope="col" class="text-end">總數量</th> --}}
                        <th scope="col" class="text-end">總營業額</th>
                        <th scope="col" class="text-end">總毛利</th>
                        <th scope="col" class="text-end" style="min-width: 100px;">毛利佔比</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total_gross_profit = 0;
                        $total_price = 0;
                        $total_qty = 0;
                    @endphp
                    @foreach ($product as $key => $data)
                        @php

                            $total_gross_profit += $data->gross_profit;
                            $total_price += $data->price;
                            $total_qty += $data->qty;
                          
                        @endphp
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td class="wrap lh-sm"> 
                                {{ $data->category }}
                            </td>
                            {{-- <td class="text-end">
                                <x-b-number :val="$data->qty"/>
                            </td> --}}
                            <td class="text-end">
                                <x-b-number :val="$data->price" prefix="$" />
                            </td>
                            <td class="text-end">
                                <x-b-number :val="$data->gross_profit" prefix="$" />
                            </td>
                            <td>
                                <div class="-percent"></div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">合計</th>
                        {{-- <th class="text-end">
                            <x-b-number :val="$total_qty" />
                        </th> --}}
                        <th class="text-end">
                            <x-b-number :val="$total_price" prefix="$" />
                        </th>
                        <th class="text-end">
                            <x-b-number :val="$total_gross_profit" prefix="$" />
                        </th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div id="-chart" class="card shadow p-4 mb-4 -page" hidden>
        <div style="margin-bottom: 20px;position: relative;height: 600px;">
            <canvas id="barChart"></canvas>
        </div>
        <canvas id="pieChart" style="max-height:800px;"></canvas>
    </div>
@endsection
@once
    @push('sub-styles')
        <style>
            .-percent {
                height: 40px;
                width: 0;
                position: relative;
                background-color: rgba(0, 161, 230, 0.3);
                margin: auto;
                margin-right: 0;
            }
            .-percent::after {
                content: attr(data-percent);
                display: block;
                position: absolute;
                right: 0;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            const data = @json($product);
            const total_gross_profit = @json($total_gross_profit);
            // console.log(data);
            const filterData = _.filter(data, (d) => (d.gross_profit >= 0));
            const categorys = _.map(data, 'category');    // 類別
            const gross_profits = _.map(data, 'gross_profit');    // 毛利

            const basePercent = _.round((data[0].gross_profit / total_gross_profit) * 100, 2);
            $('.-percent').each(function (index, element) {
                // element == this
                const percent = _.round((data[index].gross_profit / total_gross_profit) * 100, 2);
                $(element)
                    .attr('data-percent', percent + '%')
                    .css('width', Math.abs(percent/basePercent*100) + '%');
                if (data[index].gross_profit < 0) {
                    $(element).css({
                        'background-color': 'rgba(255, 101, 130, 0.3)',
                        color: 'rgb(var(--bs-danger-rgb))'
                    });
                }
            });

            const colorBlue = '0, 161, 230';
            const colorRed = '255, 101, 130';
            const positive = filterData.length;
            const negative = gross_profits.length - positive;
            const bgColor = _.map(gross_profits, (n, index) => {
                const rgb = n >= 0 ? colorBlue : colorRed;
                const a = n >= 0 ? ((positive - index) / positive) : ((index - positive + 1) / negative);
                return `rgba(${rgb}, ${a})`;
            });
            // 長條圖
            new Chart('barChart', {
                type: 'bar',
                data: {
                    labels: categorys,
                    datasets: [{
                        label: '總毛利',
                        data: gross_profits,
                        backgroundColor: bgColor
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            // display: false
                            ticks: {
                                autoSkip: false,
                                maxRotation: 90,
                                minRotation: 90
                            }
                        },
                        y: {
                            suggestedMin: 0,
                            ticks: {
                                stepSize: 100
                            },
                            grid: {
                                tickLength: 20
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: '總毛利',
                            font: {
                                size: 16
                            }
                        },
                        tooltip: {
                            callbacks: {
                                beforeBody: (tooltipItems) => {
                                    return `總營業額：$${formatNumber(data[tooltipItems[0].dataIndex].price)}`;
                                },
                                label: (tooltipItem) => {
                                    let label = tooltipItem.dataset.label || '';
                                    if (label) label += '：';
                                    label += '$' + tooltipItem.formattedValue;
                                    label += ` (${_.round((tooltipItem.parsed.y / total_gross_profit) * 100, 2)}%)`;
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
            // 圓餅圖
            new Chart('pieChart', {
                type: 'pie',
                data: {
                    labels: categorys,
                    datasets: [{
                        label: '總毛利',
                        data: gross_profits,
                        backgroundColor: bgColor
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'start'
                        },
                        title: {
                            display: true,
                            text: '總毛利',
                            font: {
                                size: 16
                            }
                        },
                        tooltip: {
                            callbacks: {
                                beforeBody: (tooltipItems) => {
                                    return `總營業額：$${formatNumber(data[tooltipItems[0].dataIndex].price)}`;
                                },
                                label: (tooltipItem) => {
                                    let label = tooltipItem.dataset.label || '';
                                    if (label) label += '：';
                                    label += '$' + tooltipItem.formattedValue;
                                    label += ` (${_.round((tooltipItem.parsed / total_gross_profit) * 100, 2)}%)`;
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
            
            // Tabs
            $('.nav-link').off('click').on('click', function() {
                const $this = $(this);
                const page = $this.data('page');

                // tab
                $('.nav-link').removeClass('active').removeAttr('aria-current');
                $this.addClass('active').attr('aria-current', 'page');
                // page
                $('.-page').prop('hidden', true);
                $(`#-${page}`).prop('hidden', false);
            });
        </script>
    @endpush
@endOnce
