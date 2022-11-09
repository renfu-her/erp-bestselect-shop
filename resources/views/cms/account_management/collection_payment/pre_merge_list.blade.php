@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-4">合併付款</h2>

    <form id="search" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">客戶</label>
                    <select class="form-select -select2 -single" name="payee_key" aria-label="客戶" data-placeholder="請選擇客戶">
                        <option value="" selected>不限</option>
                        @foreach ($payee as $value)
                            <option value="{{ $value['id'] . '|' . $value['name'] }}" {{ $value['id'] . '|' . $value['name'] == $cond['payee_key'] ? 'selected' : '' }}>{{ $value['name'] . ' - ' . (isset($value['title']) ? $value['title'] . ' ' : '') . ($value['email'] ?? $value['id']) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">付款單號</label>
                    <input class="form-control" type="text" name="po_sn" value="{{ $cond['po_sn'] }}" placeholder="請輸入付款單號">
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">單據編號</label>
                    <input class="form-control" type="text" name="source_sn" value="{{ $cond['source_sn'] }}" placeholder="請輸入單據編號">
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">付款金額</label>
                    <div class="input-group has-validation">
                        <input type="number" step="1" min="0" class="form-control @error('po_min_price') is-invalid @enderror" 
                            name="po_min_price" value="{{ $cond['po_min_price'] }}" placeholder="起始金額" aria-label="付款起始金額">
                        <input type="number" step="1" min="0" class="form-control @error('po_max_price') is-invalid @enderror" 
                            name="po_max_price" value="{{ $cond['po_max_price'] }}" placeholder="結束金額" aria-label="付款結束金額">
                        <div class="invalid-feedback">
                            @error('po_min_price')
                                {{ $message }}
                            @enderror
                            @error('po_max_price')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                {{--
                <div class="col-12 mb-3">
                    <label class="form-label">付款日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('po_sdate') is-invalid @enderror" name="po_sdate" value="{{ $cond['po_sdate'] }}" aria-label="付款起始日期">
                        <input type="date" class="form-control -endDate @error('po_edate') is-invalid @enderror" name="po_edate" value="{{ $cond['po_edate'] }}" aria-label="付款結束日期">
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('po_sdate')
                                {{ $message }}
                            @enderror
                            @error('po_edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <fieldset class="col-12 mb-3">
                    <legend class="col-form-label p-0 mb-2">付款狀態</legend>
                    <div class="px-1 pt-1">
                        @foreach ($balance_status as $key => $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="check_balance" type="radio" value="{{ $key }}" {{ (string)$key == $cond['check_balance'] ? 'checked' : '' }}>
                                    {{ $value }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
                --}}
            </div>

            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $data_per_page }}" />
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>

    <form method="POST" action="{{ $form_action }}">
        @csrf
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
                <table class="table tableList border-bottom">
                    <thead class="small align-middle">
                        <tr>
                            <th scope="col" class="text-center"><input class="form-check-input" type="checkbox" id="checkAll"></th>
                            <th scope="col" style="width:40px">#</th>
                            <th scope="col">付款單號</th>
                            <th scope="col">付款<br class="d-block d-lg-none">對象</th>
                            <th scope="col">會計科目</th>
                            <th scope="col">摘要</th>
                            <th scope="col" class="text-end">借方</th>
                            <th scope="col" class="text-end">貸方</th>
                            <th scope="col">付款日期</th>
                        </tr>
                    </thead>
                    <tbody class="data_list">
                        @foreach ($dataList as $key => $data)
                            @php
                                $rows = count($data->debit) + count($data->credit) + 1;
                            @endphp
                            <tr>
                                <th class="text-center" rowspan="{{ $rows }}">
                                    @if(count($data->credit) == 0)
                                    <input class="form-check-input single_select" type="checkbox" name="selected[{{ $key }}]" value="{{ $data->po_id }}">
                                    <input type="hidden" name="po_id[{{ $key }}]" class="select_input" value="{{ $data->po_id }}" disabled>

                                    <input type="hidden" name="amt_net[{{ $key }}]" class="select_input" value="{{ $data->po_price }}" disabled>
                                    @endif
                                </th>
                                <td rowspan="{{ $rows }}">{{ $key + 1 }}</td>
                                <td rowspan="{{ $rows }}">
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
                                <td rowspan="{{ $rows }}" class="wrap">{{ $data->po_target_name }}</td>

                                <td class="p-0 border-bottom-0" height="0"></td>
                                <td class="p-0 border-bottom-0" height="0"></td>
                                <td class="p-0 border-bottom-0" height="0"></td>
                                <td class="p-0 border-bottom-0" height="0"></td>

                                <td rowspan="{{ $rows }}">{{ $data->payment_date ? date('Y/m/d', strtotime($data->payment_date)) : '-' }}</td>
                            </tr>
                            @foreach ($data->debit as $d_value)
                                <tr>
                                    <td class="table-warning wrap ps-2">
                                        {{$d_value->account_code}} {{$d_value->account_name}}
                                    </td>
                                    <td class="table-warning wrap">
                                        @if($d_value->d_type == 'logistics')
                                            {{ $d_value->summary ?? $d_value->account_name }} {{ $data->source_sn }}
                                        @elseif($d_value->d_type == 'discount')
                                            {{$d_value->discount_title}} - {{$data->source_sn}}
                                        @else
                                            {{$d_value->product_title}}({{ $d_value->product_price }} * {{$d_value->product_qty}})({{ $d_value->product_owner }}) - {{$data->source_sn}}
                                        @endif
                                    </td>
                                    <td class="table-warning wrap text-end">
                                        {{ number_format($d_value->price) }}
                                    </td>
                                    <td class="table-warning wrap pe-2 text-end"></td>
                                </tr>
                            @endforeach
                            @foreach ($data->credit as $c_value)
                                <tr>
                                    <td class="wrap ps-2">
                                        {{$c_value->account_code}} {{$c_value->account_name}}
                                    </td>
                                    <td class="wrap">
                                        @if($c_value->payable_type == 0)
                                        {{$c_value->method_name}}{{$c_value->summary ? ' - ' . $c_value->summary : ''}}{{$c_value->note ? ' - ' . $c_value->note : ''}} - {{ $data->source_sn }} - {{ $po_sn[0] }}
                                        @else
                                        {{$c_value->method_name}}{{$c_value->summary ? ' - ' . $c_value->summary : ''}}{{$c_value->note ? ' - ' . $c_value->note : ''}} - {{ $data->source_sn }} - {{ count($po_sn) > 1 ? $po_sn[1] : $po_sn[0]  }}
                                        @endif
                                    </td>
                                    <td class="wrap text-end"></td>
                                    <td class="wrap pe-2 text-end">
                                        {{ number_format($c_value->price) }}
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row flex-column-reverse flex-sm-row">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4 submit" disabled="disabled">確認</button>
                <a href="{{ route('cms.collection_payment.index') }}" class="btn btn-outline-primary px-4" role="button">
                    返回上一頁
                </a>
            </div>

            <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
                @if($dataList)
                    <div class="mx-3">共 {{ $dataList->lastPage() }} 頁(共找到 {{ $dataList->total() }} 筆資料)</div>
                    {{-- 頁碼 --}}
                    <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
                @endif
            </div>
        </div>
    </form>
@endsection

@once
    @push('sub-styles')
        <style>
            .table-warning {
                --bs-table-bg: #fff3cd;
                --bs-table-striped-bg: #f2e7c3;
                --bs-table-striped-color: #000;
                --bs-table-active-bg: #e6dbb9;
                --bs-table-active-color: #000;
                --bs-table-hover-bg: #ece1be;
                --bs-table-hover-color: #000;
                color: #000;
                background-color: var(--bs-table-bg);
            }
            .tableList > tbody th, .tableList > tbody td {
                vertical-align: top;
            }
            .tableList > :not(caption) > * > * {
                line-height: initial;
            }
            .tableList > tbody > * > * {
                line-height: 1.6;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            $(function() {
                // 顯示筆數
                $('#dataPerPageElem').on('change', function(e) {
                    $('input[name=data_per_page]').val($(this).val());
                    $('#search').submit();
                });

                localStorage.setItem('collection_payment_claim_url', location.pathname + location.search);


                $('#checkAll').change(function(){
                    $all = $(this)[0];
                    $('.data_list tr').each(function( index ) {
                        if($(this).is(':visible')){
                            $(this).find('th input.single_select').prop('checked', $all.checked);

                            $('.submit').prop('disabled', $('input.single_select:checked').length == 0);

                            $(this).find('input.select_input').prop('disabled', $(this).find('th input.single_select:checked').length == 0);
                        }
                    });

                    // $('.single_select').prop('checked', this.checked);
                });


                $('.single_select').click(function(){
                    $('.submit').prop('disabled', $('input.single_select:checked').length == 0);
                    $(this).parents('tr').find('input.select_input').prop('disabled', !this.checked);
                });
            });
        </script>
    @endpush
@endonce
