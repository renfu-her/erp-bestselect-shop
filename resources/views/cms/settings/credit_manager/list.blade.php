@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">信用卡作業管理</h2>

    <fieldset class="col-12 mb-2">
        <div class="p-2 border rounded">
            <a href="{{ Route('cms.credit_manager.ask') }}" class="btn btn-primary" role="button">整批請款</a>
            <a href="{{ Route('cms.credit_manager.claim') }}" class="btn btn-primary" role="button">整批入款</a>
            <a href="{{ Route('cms.credit_percent.index') }}" class="btn btn-success" role="button">請款比例列表</a>
            <a href="{{ Route('cms.credit_bank.index') }}" class="btn btn-primary" role="button">銀行列表</a>
            <a href="{{ Route('cms.credit_card.index') }}" class="btn btn-danger" role="button">信用卡列表</a>
        </div>
    </fieldset>

    <form id="search" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">銀行名稱</label>
                    <select class="form-select -select2 -single" name="bank_id" aria-label="銀行名稱" data-placeholder="請選擇銀行名稱">
                        <option value="" selected>不限</option>
                        @foreach ($bank as $key => $value)
                            <option value="{{ $key }}" {{ in_array($key, $cond['bank_id']) ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">結帳地區</label>
                    <select class="form-select -select2 -single" name="area_id" aria-label="結帳地區" data-placeholder="請選擇結帳地區">
                        <option value="" selected>不限</option>
                        @foreach ($checkout_area as $key => $value)
                            <option value="{{ $key }}" {{ in_array($key, $cond['area_id']) ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">信用卡別</label>
                    <select class="form-select -select2 -single" name="card_type_id" aria-label="信用卡別" data-placeholder="請選擇信用卡別">
                        <option value="" selected>不限</option>
                        @foreach ($card_type as $key => $value)
                            <option value="{{ $key }}" {{ in_array($key, $cond['card_type_id']) ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">信用卡號</label>
                    <input class="form-control" type="text" name="card_number" value="{{ $cond['card_number'] }}" placeholder="請輸入信用卡號">
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">持卡人</label>
                    <input class="form-control" type="text" name="card_owner" value="{{ $cond['card_owner'] }}" placeholder="請輸入持卡人">
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">刷卡金額</label>
                    <div class="input-group has-validation">
                        <input type="number" step="1" min="0" class="form-control @error('authamt_min_price') is-invalid @enderror" name="authamt_min_price" value="{{ $cond['authamt_min_price'] }}" aria-label="刷卡起始金額">
                        <input type="number" step="1" min="0" class="form-control @error('authamt_max_price') is-invalid @enderror" name="authamt_max_price" value="{{ $cond['authamt_max_price'] }}" aria-label="刷卡結束金額">
                        <div class="invalid-feedback">
                            @error('authamt_min_price')
                                {{ $message }}
                            @enderror
                            @error('authamt_max_price')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">信用卡狀態</legend>
                    <div class="px-1 pt-1">
                        @foreach (['', 0, 1, 2] as $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="status_code" type="radio" value="{{ $value }}" {{ (string)$value == $cond['status_code'] ? 'checked' : '' }}>
                                    @if($value === '') 不限
                                    @elseif($value === 0) 刷卡
                                    @elseif($value === 1) 請款
                                    @elseif($value === 2) 入款
                                    @endif

                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">交易狀態</legend>
                    <div class="px-1 pt-1">
                        @foreach (['', 'online', 'offline'] as $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="mode" type="radio" value="{{ $value }}" {{ (string)$value == $cond['mode'] ? 'checked' : '' }}>
                                    @if($value == '') 不限
                                    @elseif($value == 'online') 線上
                                    @elseif($value == 'offline') 線下
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>

                <div class="col-12 mb-3">
                    <label class="form-label">刷卡日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('checkout_sdate') is-invalid @enderror" name="checkout_sdate" value="{{ $cond['checkout_sdate'] }}" aria-label="刷卡起始日期" />
                        <input type="date" class="form-control -endDate @error('checkout_edate') is-invalid @enderror" name="checkout_edate" value="{{ $cond['checkout_edate'] }}" aria-label="刷卡結束日期" />
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('checkout_sdate')
                                {{ $message }}
                            @enderror
                            @error('checkout_edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">入款日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('posting_sdate') is-invalid @enderror" name="posting_sdate" value="{{ $cond['posting_sdate'] }}" aria-label="入款起始日期" />
                        <input type="date" class="form-control -endDate @error('posting_edate') is-invalid @enderror" name="posting_edate" value="{{ $cond['posting_edate'] }}" aria-label="入款結束日期" />
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('posting_sdate')
                                {{ $message }}
                            @enderror
                            @error('posting_edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
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
                        <th scope="col">持卡人</th>
                        <th scope="col">卡號</th>
                        <th scope="col">刷卡金額</th>
                        <th scope="col">狀態</th>
                        <th scope="col">刷卡日期</th>
                        <th scope="col">卡別</th>
                        <th scope="col">收款單號</th>
                        <th scope="col">線上交易</th>
                        <th scope="col">入款日期</th>
                        <th scope="col">入款單號</th>
                        <th scope="col">結帳地區</th>
                        <th scope="col">請款銀行</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data_list as $key => $data)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $data->credit_card_owner_name }}</td>
                            <td><a href="{{ route('cms.credit_manager.record', ['id'=>$data->credit_card_received_id])}}">{{ $data->credit_card_number }}</a></td>
                            <td>{{ number_format($data->credit_card_price) }}</td>
                            <td>{{ $data->credit_card_status_code == 0 ? '刷卡' : ($data->credit_card_status_code == 1 ? '請款' : '入款') }}</td>
                            <td>{{ $data->credit_card_checkout_date ? date('Y-m-d', strtotime($data->credit_card_checkout_date)) : '' }}</td>
                            <td>{{ $data->credit_card_type }}</td>
                            <td>
                                @if($data->ro_source_type == 'ord_orders')
                                <a href="{{ route('cms.collection_received.receipt', ['id' => $data->ro_source_id]) }}">{{ $data->ro_sn }}</a>
                                @elseif($data->ro_source_type == 'csn_orders')
                                <a href="{{ route('cms.ar_csnorder.receipt', ['id' => $data->ro_source_id]) }}">{{ $data->ro_sn }}</a>
                                @elseif($data->ro_source_type == 'ord_received_orders')
                                <a href="{{ route('cms.account_received.ro-receipt', ['id' => $data->ro_source_id]) }}">{{ $data->ro_sn }}</a>
                                @elseif($data->ro_source_type == 'acc_request_orders')
                                <a href="{{ route('cms.request.ro-receipt', ['id' => $data->ro_source_id]) }}">{{ $data->ro_sn }}</a>
                                @endif
                            </td>
                            <td>{!! $data->credit_card_checkout_mode == 'online' ? '<i class="bi bi-check-lg"></i>' : '<i class="bi bi-x-lg"></i>' !!}</td>
                            <td>{{ $data->credit_card_posting_date ? date('Y-m-d', strtotime($data->credit_card_posting_date)) : '' }}</td>
                            <td><a href="{{ $data->io_id ? route('cms.credit_manager.income-detail', ['id' => $data->io_id]) : 'javascript:void(0);'}}">{{ $data->io_sn }}</a></td>
                            <td>{{ $data->credit_card_area }}</td>
                            <td>{{ $data->bank_name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            @if($data_list)
                <div class="mx-3">共 {{ $data_list->lastPage() }} 頁(共找到 {{ $data_list->total() }} 筆資料)</div>
                {{-- 頁碼 --}}
                <div class="d-flex justify-content-center">{{ $data_list->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@once
    @push('sub-styles')
        <style>

        </style>
    @endpush

    @push('sub-scripts')
        <script>
            $(function() {
                // 顯示筆數選擇
                $('#dataPerPageElem').on('change', function(e) {
                    $('input[name=data_per_page]').val($(this).val());
                    $('#search').submit();
                });
            });
        </script>
    @endpush
@endonce
