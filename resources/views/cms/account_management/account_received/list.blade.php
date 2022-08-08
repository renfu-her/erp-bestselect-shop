@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">應收帳款查詢</h2>

    <form id="search" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">會計科目</label>
                    <select class="form-select -select2 -single" name="account_received_grade_id" aria-label="會計科目" data-placeholder="請選擇會計科目">
                        <option value="" selected>不限</option>
                        @foreach ($account_received_grade as $value)
                            <option value="{{ $value->grade_id }}" {{ in_array($value->grade_id, $cond['account_received_grade_id']) ? 'selected' : '' }}>{{ $value->grade_code }} {{ $value->grade_name }}</option>
                        @endforeach
                    </select>
                </div>

                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">應收帳款狀態</legend>
                    <div class="px-1 pt-1">
                        @foreach (['', 0, 1] as $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="status_code" type="radio" value="{{ $value }}" {{ (string)$value == $cond['status_code'] ? 'checked' : '' }}>
                                    @if($value === '') 不限
                                    @elseif($value === 0) 未入款
                                    @elseif($value === 1) 已入款
                                    @endif

                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>

                <div class="col-12 mb-3">
                    <label class="form-label">收款單日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('ro_created_sdate') is-invalid @enderror" name="ro_created_sdate" value="{{ $cond['ro_created_sdate'] }}" aria-label="收款單起始日期">
                        <input type="date" class="form-control -endDate @error('ro_created_edate') is-invalid @enderror" name="ro_created_edate" value="{{ $cond['ro_created_edate'] }}" aria-label="收款單結束日期">
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('ro_created_sdate')
                                {{ $message }}
                            @enderror
                            @error('ro_created_edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                {{--
                <div class="col-12 col-sm-12 mb-3">
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
                --}}
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
                        <th scope="col">對象</th>
                        <th scope="col">會計科目</th>
                        <th scope="col">摘要</th>
                        <th scope="col">金額</th>
                        <th scope="col">狀態</th>
                        <th scope="col">日期</th>
                        <th scope="col">銷帳單號</th>
                        <th scope="col">單據編號</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data_list as $key => $data)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td><a href="{{ route('cms.account_received.claim', ['type'=>'t', 'id'=>$data->ro_target_id, 'key'=>$data->ro_target_name])}}">{{ $data->ro_target_name }}</a></td>
                            <td><a href="{{ route('cms.account_received.claim', ['type'=>'g', 'id'=>$data->ro_received_grade_id, 'key'=>$data->ro_received_grade_name])}}">{{ $data->ro_received_grade_code }} {{ $data->ro_received_grade_name }}</a></td>
                            <td>{{ $data->summary }}</td>
                            <td>{{ number_format($data->tw_price) }}</td>
                            <td>{!! $data->account_status_code == 0 ? '<span class="text-danger">未入款</span>' : '已入款' !!}</td>
                            <td>{{ $data->ro_created ? date('Y-m-d', strtotime($data->ro_created)) : '' }}</td>
                            <td>
                                @if($data->append_ro_source_type == 'ord_received_orders' && $data->account_status_code == 0)
                                <a href="{{ route('cms.account_received.ro-edit', ['id' => $data->append_ro_source_id]) }}">{{ $data->append_ro_sn }}</a>
                                @elseif($data->append_ro_source_type == 'ord_received_orders' && $data->account_status_code == 1)
                                <a href="{{ route('cms.account_received.ro-receipt', ['id' => $data->append_ro_source_id]) }}">{{ $data->append_ro_sn }}</a>
                                @endif
                            </td>
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
