@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-4">收款單查詢</h2>

    <form id="search" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">客戶</label>
                    <select class="form-select -select2 -single" name="drawee_key" aria-label="客戶" data-placeholder="請選擇客戶">
                        <option value="" selected>不限</option>
                        @foreach ($drawee as $value)
                            <option value="{{ $value['id'] . '|' . $value['name'] }}" {{ $value['id'] . '|' . $value['name'] == $cond['drawee_key'] ? 'selected' : '' }}>{{ $value['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">收款單號</label>
                    <input class="form-control" type="text" name="ro_sn" value="{{ $cond['ro_sn'] }}" placeholder="請輸入收款單號">
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">單據編號</label>
                    <input class="form-control" type="text" name="source_sn" value="{{ $cond['source_sn'] }}" placeholder="請輸入單據編號">
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">收款金額</label>
                    <div class="input-group has-validation">
                        <input type="number" step="1" min="0" class="form-control @error('r_order_min_price') is-invalid @enderror" name="r_order_min_price" value="{{ $cond['r_order_min_price'] }}" aria-label="收款起始金額">
                        <input type="number" step="1" min="0" class="form-control @error('r_order_max_price') is-invalid @enderror" name="r_order_max_price" value="{{ $cond['r_order_max_price'] }}" aria-label="收款結束金額">
                        <div class="invalid-feedback">
                            @error('r_order_min_price')
                                {{ $message }}
                            @enderror
                            @error('r_order_max_price')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">入款日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('r_order_sdate') is-invalid @enderror" name="r_order_sdate" value="{{ $cond['r_order_sdate'] }}" aria-label="入款起始日期">
                        <input type="date" class="form-control -endDate @error('r_order_edate') is-invalid @enderror" name="r_order_edate" value="{{ $cond['r_order_edate'] }}" aria-label="入款結束日期">
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('r_order_sdate')
                                {{ $message }}
                            @enderror
                            @error('r_order_edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">收款日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('order_sdate') is-invalid @enderror" name="order_sdate" value="{{ $cond['order_sdate'] }}" aria-label="收款起始日期">
                        <input type="date" class="form-control -endDate @error('order_edate') is-invalid @enderror" name="order_edate" value="{{ $cond['order_edate'] }}" aria-label="收款結束日期">
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('order_sdate')
                                {{ $message }}
                            @enderror
                            @error('order_edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <fieldset class="col-12 col-sm-4 mb-3">
                    <legend class="col-form-label p-0 mb-2">入款審核狀態</legend>
                    <div class="px-1 pt-1">
                        @foreach ($check_review_status as $key => $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="check_review" type="radio" value="{{ $key }}" {{ (string)$key == $cond['check_review'] ? 'checked' : '' }}>
                                    {{ $value }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
            </div>

            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $data_per_page }}">
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
                        <th scope="col">單號</th>
                        <th scope="col">收款對象</th>
                        <th scope="col">科目</th>
                        <th scope="col">摘要</th>
                        <th scope="col">收款金額</th>
                        <th scope="col">業務員</th>
                        <th scope="col">部門</th>
                        <th scope="col">審核日期</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td><a href="{{ $data->ro_url_link }}">{{ $data->ro_sn }}</a></td>
                            <td>{{ $data->ro_target_name }}</td>
                            <td class="p-0">
                                @foreach($data->debit as $d_value)
                                <span class="border-bottom bg-warning d-block p-1">{{ $d_value->account_code }} {{ $d_value->account_name }}</span>
                                @endforeach

                                @foreach($data->credit as $c_value)
                                <span class="border-bottom d-block bg-white p-1">{{ $c_value->account_code }} {{ $c_value->account_name }}</span>
                                @endforeach
                            </td>

                            <td class="p-0">
                                @foreach($data->debit as $d_value)
                                    @if($d_value->received_info)
                                    <span class="border-bottom bg-warning d-block p-1">
                                        @if($d_value->received_info->received_method == 'credit_card')
                                            {{ $d_value->method_name }}{{$d_value->received_info->credit_card_number ? ' - ' . $d_value->received_info->credit_card_number : ''}} - {{ $data->source_sn }}
                                        @elseif($d_value->received_info->received_method == 'remit')
                                            {{ $d_value->method_name }} {{ $d_value->account_code . ' - ' . $d_value->account_name . '（' . $d_value->received_info->remit_memo . '）'}} - {{ $data->source_sn }}
                                        @else
                                            {{ $d_value->method_name }}{{$d_value->note ? ' - ' . $d_value->note : ''}} - {{ $data->source_sn }}
                                        @endif
                                    </span>
                                    @endif
                                @endforeach

                                @foreach($data->credit as $c_value)
                                <span class="border-bottom d-block bg-white p-1">
                                    @if($c_value->d_type == 'logistics')
                                        {{ $c_value->account_name }} - {{ $data->source_sn }}
                                    @elseif($c_value->d_type == 'discount')
                                        {{ $c_value->discount_title }} - {{ $data->source_sn }}
                                    @elseif($c_value->d_type == 'product')
                                        {{ $c_value->product_title }}({{ $c_value->product_price }} * {{ $c_value->product_qty }}) - {{ $data->source_sn }}
                                    @else
                                        {{ $c_value->method_name }}{{ $c_value->note ? ' - ' . $c_value->note : '' }} - {{ $data->source_sn }}
                                    @endif
                                </span>
                                @endforeach
                            </td>

                            <td class="p-0 text-end">
                                @foreach($data->debit as $d_value)
                                <span class="border-bottom bg-warning d-block p-1">{{ number_format($d_value->price) }}</span>
                                @endforeach

                                @foreach($data->credit as $c_value)
                                <span class="border-bottom d-block bg-white p-1">{{ number_format($c_value->price) }}</span>
                                @endforeach
                            </td>

                            <td>{{-- 業務員 --}}</td>
                            <td>{{-- 部門 --}}</td>
                            <td class="bg-success">{{ $data->ro_receipt_date ? date('Y-m-d', strtotime($data->ro_receipt_date)) : '0000-00-00' }}</td>
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
            tr td span:last-child {
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
