@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-4">退回付款單查詢</h2>

    <form id="search" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">客戶</label>
                    <select class="form-select -select2 -single" name="payee_key" aria-label="客戶" data-placeholder="請選擇客戶">
                        <option value="" selected>不限</option>
                        @foreach ($payee as $value)
                            <option value="{{ $value['id'] . '|' . $value['name'] }}" {{ $value['id'] . '|' . $value['name'] == $cond['payee_key'] ? 'selected' : '' }}>{{ $value['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">付款單號</label>
                    <input class="form-control" type="text" name="po_sn" value="{{ $cond['po_sn'] }}" placeholder="請輸入付款單號">
                </div>

                <fieldset class="col-12 col-sm-4 mb-3">
                    <legend class="col-form-label p-0 mb-2">付款狀態</legend>
                    <div class="px-1 pt-1">
                        @foreach ($check_balance_status as $key => $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="check_balance" type="radio" value="{{ $key }}" {{ (string)$key == $cond['check_balance'] ? 'checked' : '' }}>
                                    {{ $value }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>

                <div class="col-12 mb-3">
                    <label class="form-label">建立日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('po_created_sdate') is-invalid @enderror" name="po_created_sdate" value="{{ $cond['po_created_sdate'] }}" aria-label="建立起始日期">
                        <input type="date" class="form-control -endDate @error('po_created_edate') is-invalid @enderror" name="po_created_edate" value="{{ $cond['po_created_edate'] }}" aria-label="建立結束日期">
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('po_created_sdate')
                                {{ $message }}
                            @enderror
                            @error('po_created_edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $data_per_page }}" />
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>

    <div class="card shadow p-4 mb-4">
        <div class="row justify-content-end mb-4">
            <div class="col-auto">
                顯示
                <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                    @foreach (config('global.dataPerPage') as $value)
                        <option value="{{ $value }}" @if ($data_per_page == $value) selected @endif>
                            {{ $value }}</option>
                    @endforeach
                </select>
                筆
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col">編號</th>
                        <th scope="col">收款對象</th>
                        <th scope="col">付款單號</th>
                        <th scope="col">已付/未付</th>
                        <th scope="col">對應單號</th>
                        <th scope="col">科目</th>
                        <th scope="col">摘要</th>
                        <th scope="col">收款金額</th>
                        <th scope="col">付款日期</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $data->po_target_name }}</td>

                            <td>
                                @php
                                    $po_sn = explode(',', $data->po_sn);
                                    $po_type = explode(',', $data->po_type);
                                @endphp
                                @foreach($po_sn as $po_key => $po_value)
                                <span class="d-block">
                                    @if($data->po_source_type == 'pcs_purchase')
                                    <a href="{{ route('cms.purchase.view-pay-order', ['id' => $data->po_source_id, 'type' => $po_type[$po_key]]) }}">{{ $po_value }}</a>
                                    @else
                                    <a href="{{ $data->po_url_link }}">{{ $po_value }}</a>
                                    @endif
                                </span>
                                @endforeach
                            </td>

                            <td>{{ $data->payment_date ? '已付款' : '未付款' }}</td>

                            <td><span class="d-block"><a href="{{ $data->source_url_link }}">{{ $data->source_sn }}</a></span></td>

                            <td class="p-0">
                                @foreach($data->debit as $d_value)
                                <span class="border-bottom d-block bg-warning p-2">{{$d_value->account_code}} {{$d_value->account_name}}</span>
                                @endforeach

                                {{--
                                @foreach($data->credit as $c_value)
                                <span class="border-bottom d-block bg-white p-2">{{$c_value->account_code}} {{$c_value->account_name}}</span>
                                @endforeach
                                --}}
                            </td>

                            <td class="p-0">
                                @foreach($data->debit as $d_value)
                                <span class="border-bottom d-block bg-warning p-2">
                                    @if($d_value->d_type == 'logistics')
                                        {{$d_value->account_name}} {{ $data->source_sn }}
                                    @elseif($d_value->d_type == 'discount')
                                        {{$d_value->discount_title}} - {{$data->source_sn}}
                                    @else
                                        {{$d_value->product_title}}({{ $d_value->product_price }} * {{$d_value->product_qty}})({{ $d_value->product_owner }}) - {{$data->source_sn}}
                                    @endif
                                </span>
                                @endforeach

                                {{--
                                @foreach($data->credit as $c_value)
                                @if($c_value->payable_type == 0)
                                <span class="border-bottom d-block bg-white p-2">{{$c_value->method_name}}{{$c_value->note ? ' - ' . $c_value->note : ''}} - {{ $data->source_sn }} - {{ $po_sn[0] }}</span>
                                @else
                                <span class="border-bottom d-block bg-white p-2">{{$c_value->method_name}}{{$c_value->note ? ' - ' . $c_value->note : ''}} - {{ $data->source_sn }} - {{ count($po_sn) > 1 ? $po_sn[1] : $po_sn[0]  }}</span>
                                @endif
                                @endforeach
                                --}}

                            </td>

                            <td class="p-0 text-end">
                                @foreach($data->debit as $d_value)
                                <span class="border-bottom d-flex flex-row" style="min-width:150px">
                                    <span class="bg-warning d-block p-2 w-100">{{ number_format($d_value->price) }}</span>
                                    {{-- <span class="bg-warning d-block p-2 w-50"></span> --}}
                                </span>
                                @endforeach

                                {{--
                                @foreach($data->credit as $c_value)
                                <span class="border-bottom d-flex flex-row" style="min-width:150px">
                                    <span class="d-block bg-white p-2 w-50"></span>
                                    <span class="d-block bg-white p-2 w-50">{{ number_format($c_value->price) }}</span>
                                </span>
                                @endforeach
                                --}}
                            </td>

                            <td>{{ $data->payment_date ? date('Y-m-d', strtotime($data->payment_date)) : '0000-00-00' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            @if($dataList)
                <div class="mx-3">共 {{ $dataList->lastPage() }} 頁(共找到 {{ $dataList->total() }} 筆資料)</div>
                {{-- 頁碼 --}}
                <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@once
    @push('styles')
        <style>
            tr td > span:last-child {
                border: none !important;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            // 顯示筆數
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
        </script>
    @endpush
@endonce
