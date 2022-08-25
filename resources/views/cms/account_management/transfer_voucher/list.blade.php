@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-4">轉帳傳票查詢</h2>

    <form id="search" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">公司</label>
                    <select class="form-select -select2 -single" name="company_id" aria-label="公司" data-placeholder="請選擇公司">
                        <option value="" selected>不限</option>
                        @foreach ($company as $value)
                            <option value="{{ $value->id }}" {{ in_array($value->id, $cond['company_id']) ? 'selected' : '' }}>{{ $value->company }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">單號</label>
                    <input class="form-control" type="text" name="tv_sn" value="{{ $cond['tv_sn'] }}" placeholder="請輸入單號">
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">傳票日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('voucher_sdate') is-invalid @enderror" name="voucher_sdate" value="{{ $cond['voucher_sdate'] }}" aria-label="傳票起始日期">
                        <input type="date" class="form-control -endDate @error('voucher_edate') is-invalid @enderror" name="voucher_edate" value="{{ $cond['voucher_edate'] }}" aria-label="傳票結束日期">
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('voucher_sdate')
                                {{ $message }}
                            @enderror
                            @error('voucher_edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <fieldset class="col-12 mb-3">
                    <legend class="col-form-label p-0 mb-2">傳票狀態</legend>
                    <div class="px-1 pt-1">
                        @foreach ($audit_status as $key => $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="audit_status" type="radio" value="{{ $key }}" {{ (string)$key == $cond['audit_status'] ? 'checked' : '' }}>
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
            <div class="col">
                <a href="{{ Route('cms.transfer_voucher.create') }}" class="btn btn-primary" role="button">
                    <i class="bi bi-plus-lg"></i> 新增轉帳傳票
                </a>
            </div>
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
                        <th scope="col">科目</th>
                        <th scope="col">摘要</th>
                        <th scope="col">金額</th>
                        <th scope="col">部門</th>
                        <th scope="col">審核日期</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td><a href="{{ route('cms.transfer_voucher.show', ['id' => $data->tv_id]) }}">{{ $data->tv_sn }}</a></td>

                            <td class="p-0">
                                @if($data->tv_items)
                                @foreach(json_decode($data->tv_items) as $i_value)
                                <span class="border-bottom d-block {{ $i_value->debit_credit_code == 'debit' ? 'bg-warning' : 'bg-white' }} p-2">{{ $i_value->grade_code }} {{ $i_value->grade_name }}</span>
                                @endforeach
                                @endif
                            </td>

                            <td class="p-0">
                                @if($data->tv_items)
                                @foreach(json_decode($data->tv_items) as $i_value)
                                <span class="border-bottom d-block {{ $i_value->debit_credit_code == 'debit' ? 'bg-warning' : 'bg-white' }} p-2" style="min-height: 57px">{{ $i_value->summary }}</span>
                                @endforeach
                                @endif
                            </td>

                            <td class="p-0">
                                @if($data->tv_items)
                                @foreach(json_decode($data->tv_items) as $i_value)
                                <span class="border-bottom d-block {{ $i_value->debit_credit_code == 'debit' ? 'bg-warning' : 'bg-white' }} p-2">{{ number_format($i_value->final_price) }}</span>
                                @endforeach
                                @endif
                            </td>

                            <td class="p-0">
                                @if($data->tv_items)
                                @foreach(json_decode($data->tv_items) as $i_value)
                                <span class="border-bottom d-block {{ $i_value->debit_credit_code == 'debit' ? 'bg-warning' : 'bg-white' }} p-2" style="min-height: 57px">{{ $i_value->department }}</span>
                                @endforeach
                                @endif
                            </td>

                            <td>{{ $data->tv_audit_date ? date('Y/m/d', strtotime($data->tv_audit_date)) : '-' }}</td>
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
    @push('sub-styles')
        <style>
            tr td > span:last-child {
                border: none !important;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            // 顯示筆數選擇
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
        </script>
    @endpush
@endOnce
