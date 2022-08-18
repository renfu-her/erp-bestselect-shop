@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">匯款紀錄</h2>

    <form id="search" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">收/付款單號</label>
                    <input class="form-control" type="text" name="sn" value="{{ $cond['sn'] }}" placeholder="請輸入收/付款單號">
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">匯出/入</label>
                    <select class="form-select -select2 -single" name="remit_type" aria-label="匯出/入" data-placeholder="請選擇">
                        <option value="all" selected>不限</option>
                        <option value="payable" {{ in_array('payable', [$cond['remit_type']]) ? 'selected' : '' }}>匯出</option>
                        <option value="received" {{ in_array('received', [$cond['remit_type']]) ? 'selected' : '' }}>匯入</option>

                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">匯款日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('sdate') is-invalid @enderror" name="sdate" value="{{ $cond['sdate'] }}" aria-label="起始日期" />
                        <input type="date" class="form-control -endDate @error('edate') is-invalid @enderror" name="edate" value="{{ $cond['edate'] }}" aria-label="結束日期" />
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('sdate')
                                {{ $message }}
                            @enderror
                            @error('edate')
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
                        <th scope="col">類別</th>
                        <th scope="col">單號</th>
                        <th scope="col">匯款日期</th>
                        <th scope="col">金額</th>
                        <th scope="col">會計代碼</th>
                        <th scope="col">會計科目</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data_list as $key => $data)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $data->type }}</td>
                            <td><a href="{{ Route('cms.remittance_record.detail', ['remit_id' => $data->remit_id, 'sn' => $data->sn], true) }}">{{ $data->sn }}</a></td>
                            <td>{{ $data->remit_date }}</td>
                            <td>{{ number_format($data->tw_price, 2) }}</td>
                            <td>{{ $data->code }}</td>
                            <td>{{ $data->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            @if(isset($data_list))
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
