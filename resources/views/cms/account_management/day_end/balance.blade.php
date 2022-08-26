@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">現金/銀行存款餘額</h2>

    <ul class="nav nav-tabs border-bottom-0">
        <li class="nav-item">
            <a href="{{ route('cms.day_end.index') }}" class="nav-link" role="button">日結查詢</a>
        </li>
        <li class="nav-item">
            <a href="javascript:void(0);" class="nav-link active" aria-current="page" role="button">現金/銀行存款餘額</a>
        </li>
        <li class="nav-item">
            <a href="{{ route('cms.day_end.show') }}" class="nav-link" role="button">日結明細表</a>
        </li>
    </ul>

    <form method="GET">
        <div class="card shadow p-4 mb-4">
            <div class="row mb-3">
                <div class="col-auto">
                    <label class="form-label">年度</label>
                    <select class="form-select" name="y" aria-label="年度" placeholder="請選擇年度">
                        @foreach ($year_range as $value)
                            <option value="{{ $value }}" {{ $value == $cond['y'] ? 'selected' : '' }}>{{ $value }}年</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto">
                    <label class="form-label">月份</label>
                    <select class="form-select" name="m" aria-label="月份" data-placeholder="請選擇月份">
                        @foreach ($month_rage as $value)
                            <option value="{{ $value }}" {{ $value == $cond['m'] ? 'selected' : '' }}>{{ $value }}月</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto align-self-end">
                    <button type="submit" class="btn btn-primary px-4">查詢</button>
                </div>
            </div>

            <div class="table-responsive tableOverBox mb-3">
                <table class="table table-hover tableList mb-1">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col">會計科目</th>
                            <th scope="col">金額</th>
                        </tr>
                    </thead>

                    <tbody class="pool">
                        @foreach ($data_list as $key => $value)
                            <tr>
                                <td><a href="{{ route('cms.day_end.balance_check', ['id'=>$value->grade_id, 'date'=>date('Y-m', strtotime($cond['y'] . '-' . $cond['m']))]) }}">{{ $value->grade_code . ' ' . $value->grade_name }}</a></td>
                                <td>{{ number_format($value->debit_price - $value->credit_price) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </form>
@endsection

@once
    @push('sub-styles')
        <style>

        </style>
    @endpush

    @push('sub-scripts')
        <script>
            $(function() {

            });
        </script>
    @endpush
@endonce