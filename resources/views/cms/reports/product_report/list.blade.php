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

    <div class="tab">
        <ul class="nav nav-tabs border-bottom-0">
            <li class="nav-item">
                <button class="nav-link active" type="button" data-page="detail1" aria-current="page">
                    季報表
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" type="button" data-page="profit">
                    通路分潤報表
                </button>
            </li>
            <li class="nav-item" hidden>
                <button class="nav-link disabled" type="button" data-page="chart1">
                    Chart
                </button>
            </li>
        </ul>

        {{-- 季報表 --}}
        <div id="-detail1" class="card shadow p-4 mb-4 -page">
            <div>
                <table class="table table-sm table-borderless">
                    <tr>
                        <th>上架商品總數：{{ number_format($products) }}</th>
                        <th>廠商總數：{{ number_format($suppliers) }}</th>
                    </tr>
                </table>
            </div>
            <div class="table-responsive tableOverBox">
                <table class="table table-striped mb-0 tableList">
                    <thead class="align-middle">
                        <tr>
                            <th scope="col">月份</th>
                            <th scope="col" class="text-end">總營業額</th>
                            <th scope="col" class="text-end">總毛利</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $Q_total_gross_profit = 0;
                            $Q_total_price = 0;
                        @endphp
                        @foreach ($dataList as $key => $data)
                            @php
                                $Q_total_gross_profit += $data->total_gross_profit;
                                $Q_total_price += $data->total_price;
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
                                <x-b-number :val="$Q_total_price" prefix="$" />
                            </th>
                            <th class="text-end">
                                <x-b-number :val="$Q_total_gross_profit" prefix="$" />
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- 分潤報表 --}}
        <div id="-profit" class="card shadow p-4 mb-4 -page" hidden>
            <h6 class="mb-3">全通路</h6>
            <div class="table-responsive">
                <table class="table table-bordered mb-1 align-middle">
                    <thead class="align-middle">
                        <tr>
                            <th scope="col" rowspan="2">月份</th>
                            <th scope="col" colspan="2" class="text-center lh-1 table-warning">有分潤碼</th>
                            <th scope="col" colspan="2" class="text-center lh-1 table-light">無分潤碼</th>
                            <th scope="col" colspan="2" class="text-center lh-1 table-success">總計</th>
                            <th scope="col" rowspan="2" class="text-end table-danger">總毛利</th>
                        </tr>
                        <tr class="text-center small">
                            <th scope="col" class="lh-1 p-1 table-warning ps-2">營業額</th>
                            <th scope="col" class="lh-1 p-1 table-warning">訂單數</th>
                            <th scope="col" class="lh-1 p-1 table-light">營業額</th>
                            <th scope="col" class="lh-1 p-1 table-light">訂單數</th>
                            <th scope="col" class="lh-1 p-1 table-success">營業額</th>
                            <th scope="col" class="lh-1 p-1 table-success pe-2">訂單數</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $SC_price_1 = 0;
                            $SC_price_0 = 0;
                            $SC_qty_1 = 0;
                            $SC_qty_0 = 0;
                            $SC_total_price = 0;
                            $SC_total_qty = 0;
                            $SC_total_gross_profit = 0;
                        @endphp
                        @foreach ($salechannelReport as $key => $data)
                            @php
                                $SC_price_1 += $data->price_1;
                                $SC_price_0 += $data->price_0;
                                $SC_qty_1 += $data->qty_1;
                                $SC_qty_0 += $data->qty_0;
                                $SC_total_price += $data->total_price;
                                $SC_total_qty += $data->total_qty;
                                $SC_total_gross_profit += $data->total_gross_profit;
                            @endphp
                            <tr>
                                <td>
                                    {{ $data->month }}月
                                </td>
                                <td class="table-warning text-end">
                                    <x-b-number :val="$data->price_1" prefix="$" />
                                </td>
                                <td class="table-warning text-center">
                                    <x-b-number :val="$data->qty_1" />
                                </td>
                                <td class="table-light text-end">
                                    <x-b-number :val="$data->price_0" prefix="$" />
                                </td>
                                <td class="table-light text-center">
                                    <x-b-number :val="$data->qty_0" />
                                </td>
                                <td class="table-success text-end">
                                    <x-b-number :val="$data->total_price" prefix="$" />
                                </td>
                                <td class="table-success text-center">
                                    <x-b-number :val="$data->total_qty" />
                                </td>
                                <td class="table-danger text-end">
                                    <x-b-number :val="$data->total_gross_profit" prefix="$" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>合計</th>
                            <th class="table-warning text-end">
                                <x-b-number :val="$SC_price_1" prefix="$" />
                            </th>
                            <th class="table-warning text-center">
                                <x-b-number :val="$SC_qty_1" />
                            </th>
                            <th class="table-light text-end">
                                <x-b-number :val="$SC_price_0" prefix="$" />
                            </th>
                            <th class="table-light text-center">
                                <x-b-number :val="$SC_qty_0" />
                            </th>
                            <th class="table-success text-end">
                                <x-b-number :val="$SC_total_price" prefix="$" />
                            </th>
                            <th class="table-success text-center">
                                <x-b-number :val="$SC_total_qty" />
                            </th>
                            <th class="table-danger text-end">
                                <x-b-number :val="$SC_total_gross_profit" prefix="$" />
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <hr>
            <div class="row mb-3">
                <label class="col-auto col-form-label">銷售通路</label>
                <div class="col">
                    <select id="salechannel_id" class="form-select">
                        @foreach ($SaleChannels as $key => $value)
                            <option value="{{ $value->id }}">{{ $value->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="table-responsive position-relative">
                <table id="salechannel_table" class="table table-bordered mb-1 align-middle opacity-50">
                    <thead class="align-middle">
                        <tr>
                            <th scope="col" rowspan="2">月份</th>
                            <th scope="col" colspan="2" class="text-center lh-1 table-warning">有分潤碼</th>
                            <th scope="col" colspan="2" class="text-center lh-1 table-light">無分潤碼</th>
                            <th scope="col" colspan="2" class="text-center lh-1 table-success">總計</th>
                            <th scope="col" rowspan="2" class="text-end table-danger">總毛利</th>
                        </tr>
                        <tr class="text-center small">
                            <th scope="col" class="lh-1 p-1 table-warning ps-2">營業額</th>
                            <th scope="col" class="lh-1 p-1 table-warning">訂單數</th>
                            <th scope="col" class="lh-1 p-1 table-light">營業額</th>
                            <th scope="col" class="lh-1 p-1 table-light">訂單數</th>
                            <th scope="col" class="lh-1 p-1 table-success">營業額</th>
                            <th scope="col" class="lh-1 p-1 table-success pe-2">訂單數</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>合計</th>
                            <th class="table-warning text-end"></th>
                            <th class="table-warning text-center"></th>
                            <th class="table-light text-end"></th>
                            <th class="table-light text-center"></th>
                            <th class="table-success text-end"></th>
                            <th class="table-success text-center"></th>
                            <th class="text-end table-danger"></th>
                        </tr>
                    </tfoot>
                </table>
                <div id="loading" class="text-center position-absolute start-0 end-0" 
                    style="top: 15px; display: none;">
                    <div class="spinner-border text-dark opacity-75"  role="status"
                        style="width:70px; height:70px; border-width:16px">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart --}}
        <div id="-chart1" class="card shadow p-4 mb-4 -page" hidden></div>
    </div>

    <div class="tab">
        <ul class="nav nav-tabs border-bottom-0">
            <li class="nav-item">
                <button class="nav-link active" type="button" data-page="detail2" aria-current="page">
                    類別排名
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" type="button" data-page="chart2">
                    Chart
                </button>
            </li>
        </ul>

        {{-- 類別排名 --}}
        <div id="-detail2" class="card shadow p-4 mb-4 -page">
            <div class="table-responsive tableOverBox">
                <table class="table table-sm table-striped tableList mb-0">
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
                            $P_total_gross_profit = 0;
                            $P_total_price = 0;
                            $P_total_qty = 0;
                        @endphp
                        @foreach ($product as $key => $data)
                            @php
                                
                                $P_total_gross_profit += $data->gross_profit;
                                $P_total_price += $data->price;
                                $P_total_qty += $data->qty;
                                
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
                                <x-b-number :val="$P_total_qty" />
                            </th> --}}
                            <th class="text-end">
                                <x-b-number :val="$P_total_price" prefix="$" />
                            </th>
                            <th class="text-end">
                                <x-b-number :val="$P_total_gross_profit" prefix="$" />
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Chart --}}
        <div id="-chart2" class="card shadow p-4 mb-4 -page" hidden>
            <div style="margin-bottom: 20px;position: relative;height: 600px;">
                <canvas id="barChart"></canvas>
            </div>
            <canvas id="pieChart" style="max-height:800px;"></canvas>
        </div>
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
            const product = @json($product);    // 類別排名
            const total_gross_profit = @json($P_total_gross_profit);    // 類別-總毛利合計
            const salechannelReport = @json($salechannelReport);    // 全通路
            console.log(salechannelReport);
            getSalechannelReport();

            // 毛利佔比
            const basePercent = _.round((product[0].gross_profit / total_gross_profit) * 100, 2);
            $('.-percent').each(function(index, element) {
                // element == this
                const percent = _.round((product[index].gross_profit / total_gross_profit) * 100, 2);
                $(element)
                    .attr('data-percent', percent + '%')
                    .css('width', Math.abs(percent / basePercent * 100) + '%');
                if (product[index].gross_profit < 0) {
                    $(element).css({
                        'background-color': 'rgba(255, 101, 130, 0.3)',
                        color: 'rgb(var(--bs-danger-rgb))'
                    });
                }
            });

            // Chart ****************
            const filterData = _.filter(product, (d) => (d.gross_profit >= 0));
            const categorys = _.map(product, 'category'); // 類別
            const gross_profits = _.map(product, 'gross_profit'); // 毛利
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
                                tickLength: 20,
                                color: (context) => {
                                    if (context.tick.value === 0) {
                                        return '#000000';
                                    }
                                    return '#E5E5E5';
                                }
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
                                    return `總營業額：$${formatNumber(product[tooltipItems[0].dataIndex].price)}`;
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
                                    return `總營業額：$${formatNumber(product[tooltipItems[0].dataIndex].price)}`;
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

            // Tabs ****************
            $('.nav-link').off('click').on('click', function() {
                const $this = $(this);
                const page = $this.data('page');
                const $tab = $this.closest('.tab');

                // tab
                $('.nav-link', $tab).removeClass('active').removeAttr('aria-current');
                $this.addClass('active').attr('aria-current', 'page');
                // page
                $('.-page', $tab).prop('hidden', true);
                $(`#-${page}`, $tab).prop('hidden', false);
            });

            // API - 銷售通路 ****************
            $('#salechannel_id').on('change', getSalechannelReport);
            function getSalechannelReport() {
                const $table = $('#salechannel_table');
                const $loading = $('#loading');
                $table.addClass('opacity-50');
                $loading.show();

                const _URL = '{{ Route('api.cms.order_salechannel_report') }}';
                const Data = {
                    year: {{ $cond['year'] }},
                    quarter: {{ $cond["quarter"] }},
                    salechannel_id: $('#salechannel_id').val(),
                };
                $('tbody, tfoot th:not(:first)', $table).empty();

                axios.post(_URL, Data)
                .then((result) => {
                    const res = result.data;
                    // console.log(res.data);
                    if (res.status === '0' && res.data && res.data.length > 0) {
                        const sc_datas = res.data;
                        let sums = [0, 0, 0, 0, 0, 0, 0];
                        // tbody
                        _.forEach(sc_datas, (val) => {
                            $('tbody', $table).append(`
                                <tr>
                                    <td>${val.month}月</td>
                                    <td class="table-warning text-end">$${formatNumber(val.price_1)}</td>
                                    <td class="table-warning text-center">${formatNumber(val.qty_1)}</td>
                                    <td class="table-light text-end">$${formatNumber(val.price_0)}</td>
                                    <td class="table-light text-center">${formatNumber(val.qty_0)}</td>
                                    <td class="table-success text-end">$${formatNumber(val.total_price)}</td>
                                    <td class="table-success text-center">${formatNumber(val.total_qty)}</td>
                                    <td class="table-danger text-end">$${formatNumber(val.total_gross_profit)}</td>
                                </tr>
                            `);
                            sums[0] += Number(val.price_1);
                            sums[1] += Number(val.qty_1);
                            sums[2] += Number(val.price_0);
                            sums[3] += Number(val.qty_0);
                            sums[4] += Number(val.total_price);
                            sums[5] += Number(val.total_qty);
                            sums[6] += Number(val.total_gross_profit);
                        });
                        // tfoot
                        $('tfoot th:not(:first)', $table).each(function (index, element) {
                            // element == this
                            const prefix = $(element).hasClass('text-end') ? '$' : '';
                            $(element).text(prefix + formatNumber(sums[index]));
                        });
                    }
                    $table.removeClass('opacity-50');
                    $loading.hide();
                }).catch((err) => {
                    console.error(err);
                    $table.removeClass('opacity-50');
                    $loading.hide();
                });
            }
        </script>
    @endpush
@endOnce
