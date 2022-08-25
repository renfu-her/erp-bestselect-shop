@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">日結作業</h2>

    <ul class="nav nav-tabs border-bottom-0">
        <li class="nav-item">
            <a href="javascript:void(0);" class="nav-link active" aria-current="page" role="button">日結查詢</a>
        </li>
        {{-- <li class="nav-item">
            <a href="{{ Route('cms.day_end.balance') }}" class="nav-link" role="button">現金/銀行存款餘額</a>
        </li> --}}
        <li class="nav-item">
            <a href="{{ Route('cms.day_end.show') }}" class="nav-link" role="button">日結明細表</a>
        </li>
    </ul>

    <form method="POST" action="{{ $form_action }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            @if($errors->any())
            <div class="alert alert-danger">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
            @endif

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
                    <button type="button" class="btn btn-primary px-4 query_date">查詢</button>
                </div>
            </div>

            <div class="table-responsive tableOverBox mb-3">
                <table class="table table-hover tableList mb-1">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col" class="text-center"><input class="form-check-input" type="checkbox" id="checkAll"></th>
                            <th scope="col">日期</th>
                            <th scope="col">日結日期</th>
                            <th scope="col">日結次數</th>
                            <th scope="col">日結人員</th>
                            <th scope="col">傳票張數</th>
                            <th scope="col">狀態</th>
                            <th scope="col">備註</th>
                        </tr>
                    </thead>

                    <tbody class="pool">
                        @foreach ($data_list as $key => $value)
                            <tr>
                                @php
                                    $data = $value->data ?? null;
                                @endphp
                                <th class="text-center">
                                    <input class="form-check-input single_select" type="checkbox" name="selected[{{ $key }}]" value="{{ $value->day }}">
                                    <input type="hidden" name="closing_date[{{ $key }}]" class="select_input" value="{{ $value->day }}" disabled>
                                </th>
                                <td>{{ date('Y/m/d', strtotime($value->day)) }}</td>
                                <td>{{ $data ? date('Y/m/d', strtotime($data->deo_p_date)) : '-' }}</td>
                                <td>{{ $data ? $data->deo_times : '' }}</td>
                                <td>{{ $data ? $data->clearinger_name : '' }}</td>
                                <td>{!! $data ? ( $data->deo_id ? '<a href="' . route('cms.day_end.detail', ['id'=>$data->deo_id]) . '">' . $data->deo_count . '</a>' : '' ) : '' !!}</td>
                                <td>{{ $data ? $data->deo_status : '' }}</td>
                                <td>{!! $data ? ( $data->deo_remark ? '借貸不平：<br>' . nl2br($data->deo_remark) : '' ) : '' !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4 submit" disabled="disabled">確認</button>
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
                $('#checkAll').change(function(){
                    $all = $(this)[0];
                    $('.pool tr').each(function( index ) {
                        if($(this).is(':visible')){
                            $(this).find('th input.single_select').prop('checked', $all.checked);

                            $('.submit').prop('disabled', $('input.single_select:checked').length == 0);

                            $(this).find('input.select_input').prop('disabled', $(this).find('th input.single_select:checked').length == 0);
                        }
                    });
                });

                $('.single_select').click(function(){
                    $('.submit').prop('disabled', $('input.single_select:checked').length == 0);
                    $(this).parents('tr').find('input.select_input').prop('disabled', !this.checked);
                });

                $('.query_date').click(function(){
                    const y = $('select[name="y"]').val();
                    const m = $('select[name="m"]').val();
                    const url = '{{ url()->current() }}' + '?y=' + y + '&m=' + m;
                    window.location.href = url;
                });
            });
        </script>
    @endpush
@endonce