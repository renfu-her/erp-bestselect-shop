@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-4">付款單查詢</h2>

    <form id="search" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">客戶</label>
                    <select class="form-select -select2 -single" name="supplier_id" aria-label="客戶" data-placeholder="請輸入客戶">
                        <option value="" selected>不限</option>
                        @foreach ($supplier as $value)
                            <option value="{{ $value->id }}" {{ in_array($value->id, $cond['supplier_id']) ? 'selected' : '' }}>{{ $value->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">付款單號</label>
                    <input class="form-control" type="text" name="p_order_sn" value="{{ $cond['p_order_sn'] }}" placeholder="請輸入付款單號">
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">單據編號</label>
                    <input class="form-control" type="text" name="purchase_sn" value="{{ $cond['purchase_sn'] }}" placeholder="請輸入單據編號">
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">付款金額</label>
                    <div class="input-group has-validation">
                        <input type="number" step="1" min="0" class="form-control @error('p_order_min_price') is-invalid @enderror" name="p_order_min_price" value="{{ $cond['p_order_min_price'] }}" aria-label="付款起始金額" />
                        <input type="number" step="1" min="0" class="form-control @error('p_order_max_price') is-invalid @enderror" name="p_order_max_price" value="{{ $cond['p_order_max_price'] }}" aria-label="付款結束金額" />
                        <div class="invalid-feedback">
                            @error('p_order_min_price')
                                {{ $message }}
                            @enderror
                            @error('p_order_max_price')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-3">
                    {{-- last payment_date field of acc_payable table --}}
                    <label class="form-label">付款日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('p_order_sdate') is-invalid @enderror" name="p_order_sdate" value="{{ $cond['p_order_sdate'] }}" aria-label="入款起始日期" />
                        <input type="date" class="form-control -endDate @error('p_order_edate') is-invalid @enderror" name="p_order_edate" value="{{ $cond['p_order_edate'] }}" aria-label="入款結束日期" />
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('p_order_sdate')
                                {{ $message }}
                            @enderror
                            @error('p_order_edate')
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
                        <th scope="col">付款單號</th>
                        <th scope="col">付款對象</th>
                        <th scope="col">會計科目</th>
                        <th scope="col">摘要</th>
                        <th scope="col">
                            <span class="d-flex flex-row">
                                <span class="w-50">借方</span>
                                <span class="w-50">貸方</span>
                            </span>
                        </th>
                        <th scope="col">付款日期</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                @php
                                    $po_sn = explode(',', $data->po_sn);
                                    $po_type = explode(',', $data->po_type);
                                @endphp
                                @foreach($po_sn as $po_key => $po_value)
                                <span class="d-block">
                                    <a href="{{ $data->purchase_id ? route('cms.purchase.view-pay-order', ['id' => $data->po_source_id, 'type' => $po_type[$po_key]]) : route('cms.order.pay-order', ['id' => $data->po_source_id, 'sid' => $data->po_source_sub_id]) }}">{{ $po_value }}</a>
                                </span>
                                @endforeach
                            </td>
                            <td>{{ $data->supplier_name }} - {{ $data->supplier_contact_person }}</td>
                            <td class="p-0">
                                @foreach($data->debit as $d_value)
                                <span class="border-bottom d-block bg-warning p-1">{{$d_value->account_code}} - {{$d_value->account_name}}</span>
                                @endforeach

                                @foreach($data->credit as $c_value)
                                <span class="border-bottom d-block bg-white p-1">{{$c_value->account_code}} - {{$c_value->account_name}}</span>
                                @endforeach
                            </td>

                            <td class="p-0">
                                @foreach($data->debit as $d_value)
                                <span class="border-bottom d-block bg-warning p-1">
                                    @if($d_value->d_type == 'logistics')
                                        {{$d_value->account_name}} - {{ $data->purchase_order_sn }}
                                    @elseif($d_value->d_type == 'discount')
                                        {{$d_value->discount_title}} - {{$data->purchase_order_sn}}
                                    @else
                                        {{$d_value->product_title}}({{ $d_value->product_price }} * {{$d_value->product_qty}})({{ $d_value->product_owner }}) - {{$data->purchase_order_sn}}
                                    @endif
                                </span>
                                @endforeach

                                @foreach($data->credit as $c_value)
                                @if($c_value->payable_type == 0)
                                <span class="border-bottom d-block bg-white p-1">{{$c_value->method_name}}{{$c_value->note ? ' - ' . $c_value->note : ''}} - {{ $data->purchase_order_sn }} - {{ $po_sn[0] }}</span>
                                @elseif($c_value->payable_type == 1)
                                <span class="border-bottom d-block bg-white p-1">{{$c_value->method_name}}{{$c_value->note ? ' - ' . $c_value->note : ''}} - {{ $data->purchase_order_sn }} - {{ count($po_sn) > 1 ? $po_sn[1] : $po_sn[0]  }}</span>
                                @endif
                                @endforeach
                            </td>

                            <td class="p-0 text-end">
                                @foreach($data->debit as $d_value)
                                <span class="border-bottom d-flex flex-row" style="min-width:150px">
                                    <span class="bg-warning d-block p-1 w-50">{{ number_format($d_value->price) }}</span>
                                    <span class="bg-warning d-block p-1 w-50"></span>
                                </span>
                                @endforeach

                                @foreach($data->credit as $c_value)
                                <span class="border-bottom d-flex flex-row" style="min-width:150px">
                                    <span class="d-block bg-white p-1 w-50"></span>
                                    <span class="d-block bg-white p-1 w-50">{{ number_format($c_value->price) }}</span>
                                </span>
                                @endforeach
                            </td>

                            <td class="">{{ $data->payment_date ? date('Y-m-d', strtotime($data->payment_date)) : '0000-00-00' }}</td>
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
