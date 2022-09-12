@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">應收票據</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        @can('cms.note_receivable.edit')
        <div class="p-1 pe-2">
            <a href="{{ Route('cms.note_receivable.ask', ['type'=>'collection']) }}" class="btn btn-sm btn-primary" role="button">整批託收</a>
            <a href="{{ Route('cms.note_receivable.ask', ['type'=>'nd']) }}" class="btn btn-sm btn-primary" role="button">整批次交票</a>
            <a href="{{ Route('cms.note_receivable.ask', ['type'=>'cashed']) }}" class="btn btn-sm btn-primary cc_date" role="button">整批兌現</a>
        </div>
        @endcan
    </nav>

    <form id="search" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">票據狀態</label>
                    <select class="form-select" name="cheque_status_code" aria-label="票據狀態" data-placeholder="請選擇票據狀態">
                        <option value="" selected>不限</option>
                        @foreach ($cheque_status_code as $key => $value)
                            <option value="{{ $key }}" {{ in_array($key, $cond['cheque_status_code']) ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">託收銀行</label>
                    <select class="form-select" name="banks" aria-label="託收銀行" data-placeholder="請選擇託收銀行">
                        <option value="" selected>不限</option>
                        @foreach ($banks as $key => $value)
                            <option value="{{ $value }}" {{ $value == $cond['banks'] ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">存入地區</label>
                    <select class="form-select" name="deposited_area_code" aria-label="存入地區" data-placeholder="請選擇存入地區">
                        <option value="" selected>不限</option>
                        @foreach ($checkout_area as $key => $value)
                            <option value="{{ $key }}" {{ $key == $cond['deposited_area_code'] ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">票據號碼</label>
                    <input class="form-control" type="text" name="ticket_number" value="{{ $cond['ticket_number'] }}" placeholder="請輸入票據號碼">
                </div>

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">發票人</label>
                    <input class="form-control" type="text" name="drawer" value="{{ $cond['drawer'] }}" placeholder="請輸入發票人">
                </div>

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">業務員</label>
                    <select class="form-select -select2 -single" name="undertaker" aria-label="業務員" data-placeholder="請選擇業務員">
                        <option value="" selected>不限</option>
                        @foreach ($undertaker as $key => $value)
                            <option value="{{ $value->id }}" {{ in_array($value->id, $cond['undertaker']) ? 'selected' : '' }}>{{ $value->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">金額範圍</label>
                    <div class="input-group has-validation">
                        <input type="number" step="1" min="0" class="form-control @error('received_min_price') is-invalid @enderror" 
                            name="received_min_price" value="{{ $cond['received_min_price'] }}" aria-label="起始金額" placeholder="起始金額">
                        <input type="number" step="1" min="0" class="form-control @error('received_max_price') is-invalid @enderror" 
                            name="received_max_price" value="{{ $cond['received_max_price'] }}" aria-label="結束金額" placeholder="結束金額">
                        <div class="invalid-feedback">
                            @error('received_min_price')
                                {{ $message }}
                            @enderror
                            @error('received_max_price')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">收票日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('ro_receipt_sdate') is-invalid @enderror" name="ro_receipt_sdate" value="{{ $cond['ro_receipt_sdate'] }}" aria-label="收票起始日期">
                        <input type="date" class="form-control -endDate @error('ro_receipt_edate') is-invalid @enderror" name="ro_receipt_edate" value="{{ $cond['ro_receipt_edate'] }}" aria-label="收票結束日期">
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('ro_receipt_sdate')
                                {{ $message }}
                            @enderror
                            @error('ro_receipt_edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">託收、次交日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('cheque_c_n_sdate') is-invalid @enderror" name="cheque_c_n_sdate" value="{{ $cond['cheque_c_n_sdate'] }}" aria-label="託收、次交起始日期" />
                        <input type="date" class="form-control -endDate @error('cheque_c_n_edate') is-invalid @enderror" name="cheque_c_n_edate" value="{{ $cond['cheque_c_n_edate'] }}" aria-label="託收、次交結束日期" />
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('cheque_c_n_sdate')
                                {{ $message }}
                            @enderror
                            @error('cheque_c_n_edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">到期日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('cheque_due_sdate') is-invalid @enderror" name="cheque_due_sdate" value="{{ $cond['cheque_due_sdate'] }}" aria-label="到期起始日期">
                        <input type="date" class="form-control -endDate @error('cheque_due_edate') is-invalid @enderror" name="cheque_due_edate" value="{{ $cond['cheque_due_edate'] }}" aria-label="到期結束日期">
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('cheque_due_sdate')
                                {{ $message }}
                            @enderror
                            @error('cheque_due_edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">兌現日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('cheque_cashing_sdate') is-invalid @enderror" name="cheque_cashing_sdate" value="{{ $cond['cheque_cashing_sdate'] }}" aria-label="兌現起始日期">
                        <input type="date" class="form-control -endDate @error('cheque_cashing_edate') is-invalid @enderror" name="cheque_cashing_edate" value="{{ $cond['cheque_cashing_edate'] }}" aria-label="兌現結束日期">
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('cheque_cashing_sdate')
                                {{ $message }}
                            @enderror
                            @error('cheque_cashing_edate')
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
                        <th scope="col">#</th>
                        <th scope="col">支票號碼</th>
                        <th scope="col">金額</th>
                        <th scope="col">狀態</th>
                        <th scope="col">收款單號</th>
                        <th scope="col">收票日期</th>
                        <th scope="col">託收次交日期</th>
                        <th scope="col">到期日</th>
                        <th scope="col">兌現日期</th>
                        <th scope="col">抽票日期</th>
                        <th scope="col">業務員</th>
                        <th scope="col">發票人</th>
                        <th scope="col">託收銀行</th>
                        <th scope="col">應付帳號</th>
                        <th scope="col">付款行別</th>
                        <th scope="col">存入地區</th>
                        <th scope="col">備註</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($data_list as $key => $data)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td><a href="{{ route('cms.note_receivable.record', ['id'=>$data->cheque_received_id]) }}">{{ $data->cheque_ticket_number }}</a></td>
                            <td>{{ number_format($data->tw_price) }}</td>
                            <td>{{ $data->cheque_status }}</td>
                            <td>{{ $data->ro_sn }}</td>
                            <td>{{ $data->ro_receipt_date ? date('Y/m/d', strtotime($data->ro_receipt_date)) : '' }}</td>
                            <td>{{ $data->cheque_c_n_date ? date('Y/m/d', strtotime($data->cheque_c_n_date)) : '' }}</td>
                            <td>{{ $data->cheque_due_date ? date('Y/m/d', strtotime($data->cheque_due_date)) : '' }}</td>
                            <td>{{ $data->cheque_cashing_date ? date('Y/m/d', strtotime($data->cheque_cashing_date)) : '' }}</td>
                            <td>{{ $data->cheque_draw_date ? date('Y/m/d', strtotime($data->cheque_draw_date)) : '' }}</td>
                            <td>{{ $data->ro_undertaker }}</td>
                            <td>{{ $data->cheque_drawer }}</td>
                            <td>{{ $data->cheque_banks }}</td>
                            <td>{{ $data->cheque_accounts }}</td>
                            <td></td>
                            <td>{{ $data->cheque_deposited_area }}</td>
                            <td>{{ $data->note}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row flex-column-reverse flex-sm-row">
        <div class="col-auto">
            <a href="{{ strpos(url()->full(), '?') ? url()->full() . '&action=print' : url()->full() . '?action=print' }}" target="_blank" class="btn btn-warning px-4">列印畫面</a>
        </div>

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

                $('.cc_date').on('click', function(e) {
                    // e.preventDefault();
                    const cc_sdate = $('input[name="cheque_cashing_sdate"]').val();
                    const cc_edate = $('input[name="cheque_cashing_edate"]').val();
                    if(cc_sdate || cc_edate) {
                        const new_url = $(this).attr('href') + '?cc_sdate=' + cc_sdate + '&cc_edate=' + cc_edate;
                        $(this).attr('href', new_url);
                    }
                });
            });
        </script>
    @endpush
@endonce
