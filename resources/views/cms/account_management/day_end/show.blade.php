@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">日結作業</h2>

    <ul class="nav nav-tabs border-bottom-0">
        <li class="nav-item">
            <a href="{{ route('cms.day_end.index') }}" class="nav-link" role="button">日結查詢</a>
        </li>
        <li class="nav-item">
            <a href="{{ route('cms.day_end.balance') }}" class="nav-link" role="button">現金/銀行存款餘額</a>
        </li>
        <li class="nav-item">
            <a href="javascript:void(0);" class="nav-link active" aria-current="page" role="button">日結明細表</a>
        </li>
    </ul>

    <form method="GET">
        <div class="card shadow p-4 mb-4">

            <div class="row mb-3 align-items-end">
                <div class="col">
                    <label class="form-label">日期</label>
                    <input type="date" name="current_date" class="form-control @error('current_date') is-invalid @enderror" placeholder="請輸入日期" aria-label="日期" value="{{ old('current_date', $cond['current_date'] ?? date('Y-m-d', strtotime(date('Y-m-d'))) ) }}" max="{{ date('Y-m-d', strtotime('now')) }}">
                    <div class="invalid-feedback">
                        @error('current_date')
                        {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="col-auto align-self-end">
                    <button type="submit" class="btn btn-primary px-4">查詢</button>
                </div>
            </div>

            <div class="table-responsive tableOverBox mb-3">
                @php
                    $d_total = 0;
                    $c_total = 0;
                @endphp
                @foreach($data_list as $pro_key => $pro_value)
                @if(count($pro_value) > 0)
                    <table class="table table-hover tableList">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">摘要</th>
                                <th scope="col">銀行帳戶</th>
                                <th scope="col" class="text-end">（收）</th>
                                <th scope="col" class="text-end">（支）</th>
                                <th scope="col">單據編號</th>
                                <th scope="col">傳票號碼</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $d_sub_total = 0;
                                $c_sub_total = 0;
                            @endphp
                            @foreach ($pro_value as $key => $value)
                                <tr>
                                    <td>{{ $value->summary }}</td>
                                    <td>{{ $value->grade_name }}</td>
                                    <td class="text-end">{{ $value->d_price != 0 ? number_format($value->d_price) : '' }}</td>
                                    <td class="text-end">{{ $value->c_price != 0 ? number_format($value->c_price) : '' }}</td>
                                    <td><a href="{{ $value->source_link }}">{{ $value->source_sn }}</a></td>
                                    <td>{{ $value->sn }}</td>
                                </tr>
                                @php
                                    $d_sub_total += $value->d_price;
                                    $c_sub_total += $value->c_price;
                                @endphp
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr>
                                <td>{{ $data_title[$pro_key] }}　合計</td>
                                <td></td>
                                <td class="text-end">{{ number_format($d_sub_total) }}</td>
                                <td class="text-end">{{ number_format($c_sub_total) }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            @php
                                $d_total += $d_sub_total;
                                $c_total += $c_sub_total;
                            @endphp
                        </tfoot>
                    </table>
                @endif
                @endforeach

                <table class="table table-hover tableList mb-1">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col">摘要</th>
                            <th scope="col">銀行帳戶</th>
                            <th scope="col" class="text-end">（收）</th>
                            <th scope="col" class="text-end">（支）</th>
                            <th scope="col">單據編號</th>
                            <th scope="col">傳票號碼</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <td>今日合計</td>
                            <td></td>
                            <td class="text-end">{{ number_format($d_total) }}</td>
                            <td class="text-end">{{ number_format($c_total) }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="table-responsive tableOverBox mb-3">
                <table class="table table-hover tableList mb-1">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col">銀行帳戶</th>
                            <th scope="col">前日結存</th>
                            <th scope="col">今日收入</th>
                            <th scope="col">今日支出</th>
                            <th scope="col">今日結存</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($remit as $value)
                            <tr>
                                <td>{{ $value->title }}</td>
                                <td>{{ number_format($value->pre_price) }}</td>
                                <td>{{ number_format($value->cur_debit_price) }}</td>
                                <td>{{ number_format($value->cur_credit_price) }}</td>
                                <td>{{ number_format($value->pre_price + $value->cur_price) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-responsive tableOverBox mb-3">
                <table class="table table-hover tableList mb-1">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col">類別</th>
                            <th scope="col">前日結存</th>
                            <th scope="col">張數</th>
                            <th scope="col">本日兌現</th>
                            <th scope="col">張數</th>
                            <th scope="col">本日收入</th>
                            <th scope="col">張數</th>
                            <th scope="col">本日結存</th>
                            <th scope="col">張數</th>
                            <th scope="col">次日兌現</th>
                            <th scope="col">張數</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($note_credit as $value)
                            <tr>
                                <td>{{ $value->title }}</td>
                                <td>{{ number_format($value->pre_price) }}</td>
                                <td>{{ $value->pre_count }}</td>
                                <td>{{ number_format($value->cur_cashed_price) }}</td>
                                <td>{{ $value->cur_cashed_count }}</td>
                                <td>{{ number_format($value->cur_price) }}</td>
                                <td>{{ $value->cur_count }}</td>
                                <td>{{ number_format($value->pre_price + $value->cur_price - $value->cur_cashed_price) }}</td>
                                <td>{{ $value->pre_count + $value->cur_count - $value->cur_cashed_count }}</td>
                                <td>{{ $value->nex_cashed_price }}</td>
                                <td>{{ $value->nex_cashed_count }}</td>
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