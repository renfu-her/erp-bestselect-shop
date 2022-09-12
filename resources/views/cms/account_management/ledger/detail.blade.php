@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">分類帳明細</h2>

    <div id="DivIdToPrint" class="card shadow p-4 mb-4">
        <div class="border rounded p-3 mb-3 text-center">
            <h5>喜鴻國際企業股份有限公司</h5>
            <h5>{{ $pre_data->grade_code . ' ' . $pre_data->grade_name }} 分類帳</h5>
            <p class="m-0 lh-1">起迄期間：{{ request('sdate') ? date('Y-m-d', strtotime(request('sdate'))) : '' }}～{{ request('edate') ? date('Y-m-d', strtotime(request('edate'))) : '' }}　列印日期：{{ date('Y-m-d', strtotime(date('Y-m-d'))) }}</p>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-1">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">傳票日期</th>
                        <th scope="col">總帳單號</th>
                        <th scope="col">會計單號</th>
                        <th scope="col">摘要</th>
                        <th scope="col" class="text-end">借方</th>
                        <th scope="col" class="text-end">貸方</th>
                        <th scope="col" class="text-end">餘額</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $d_count = 0;
                        $pre_net_price = $pre_data->net_price;
                    @endphp
                    <tr>
                        <th>{{ $d_count + 1 }}</th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>上期累計金額</td>
                        <td class="text-end">{{ number_format($pre_data->debit_price) }}</td>
                        <td class="text-end">{{ number_format($pre_data->credit_price) }}</td>
                        <td class="text-end"></td>
                    </tr>
                    @php
                        $d_count++;
                    @endphp
                    @foreach($data_list as $value)
                        <tr>
                            <th>{{ $d_count + 1 }}</th>
                            <td>{{ date('Y-m-d', strtotime($value->closing_date)) }}</td>
                            <td>{{ $value->sn }}</td>
                            <td><a href="{{ $value->link }}">{{ $value->source_sn }}</a></td>
                            <td>{{ $value->source_summary }}</td>
                            <td class="text-end">{{ number_format($value->debit_price) }}</td>
                            <td class="text-end">{{ number_format($value->credit_price) }}</td>
                            <td class="text-end">{{ number_format($pre_net_price + $value->net_price) }}</td>
                        </tr>
                        @php
                            $d_count++;
                            $pre_net_price += $value->net_price;
                        @endphp
                    @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <th>{{ $d_count + 1 }}</th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>上期累計金額</td>
                        <td class="text-end">{{ number_format($pre_data->debit_price) }}</td>
                        <td class="text-end">{{ number_format($pre_data->credit_price) }}</td>
                        <td class="text-end"></td>
                    </tr>
                    @php
                        $d_count++;
                    @endphp

                    <tr>
                        <th>{{ $d_count + 1 }}</th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>差額</td>
                        <td class="text-end">{{ $pre_data->net_price > 0 ? number_format($pre_data->net_price) : '' }}</td>
                        <td class="text-end">{{ $pre_data->net_price < 0 ? number_format(abs($pre_data->net_price)) : '' }}</td>
                        <td class="text-end"></td>
                    </tr>
                    @php
                        $d_count++;
                    @endphp

                    <tr>
                        <th>{{ $d_count + 1 }}</th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>本期金額</td>
                        <td class="text-end">{{ number_format($data_list->sum('debit_price')) }}</td>
                        <td class="text-end">{{ number_format($data_list->sum('credit_price')) }}</td>
                        <td class="text-end"></td>
                    </tr>
                    @php
                        $d_count++;
                    @endphp

                    <tr>
                        <th>{{ $d_count + 1 }}</th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>本期累計金額</td>
                        <td class="text-end">{{ number_format($pre_data->debit_price + $data_list->sum('debit_price')) }}</td>
                        <td class="text-end">{{ number_format($pre_data->credit_price + $data_list->sum('credit_price')) }}</td>
                        <td class="text-end"></td>
                    </tr>
                    @php
                        $d_count++;
                    @endphp

                    <tr>
                        <th>{{ $d_count + 1 }}</th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>差額</td>
                        <td class="text-end">{{ $pre_data->net_price + $data_list->sum('net_price') > 0 ? number_format($pre_data->net_price + $data_list->sum('net_price')) : '' }}</td>
                        <td class="text-end">{{ $pre_data->net_price + $data_list->sum('net_price') < 0 ? number_format(abs($pre_data->net_price + $data_list->sum('net_price'))) : '' }}</td>
                        <td class="text-end"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="col-auto">
        <button type="button" id="print" class="btn btn-warning px-4">列印畫面</button>
        <a href="{{ url()->previous() }}" class="btn btn-outline-primary px-4" role="button">
            返回上一頁
        </a>
    </div>
@endsection

@once
    @push('sub-styles')
        <style>

        </style>
    @endpush

    @push('sub-scripts')
        <script>
            const cssLink1 = @json(Asset('dist/css/app.css'));
            const cssLink2 = @json(Asset('dist/css/sub-content.css')) + '?1.0';
            $('#print').on('click',  function () {
                printDiv('#DivIdToPrint', [cssLink1, cssLink2]);
            });
        </script>
    @endpush
@endonce