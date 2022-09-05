@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-4">應收帳款入款</h2>


    <form method="POST" action="{{ $form_action }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            @if($errors->any())
            <div class="alert alert-danger mt-3">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
            @endif
            <div class="table-responsive tableOverBox">
                <table class="table table-hover tableList mb-1">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col" class="text-center"><input class="form-check-input" type="checkbox" id="checkAll"></th>
                            <th scope="col">#</th>
                            <th scope="col">單據編號</th>
                            <th scope="col">對象</th>
                            <th scope="col">會計科目</th>
                            <th scope="col">摘要</th>
                            <th scope="col">金額</th>
                            <th scope="col">狀態</th>
                            <th scope="col">日期</th>
                        </tr>
                    </thead>

                    <tbody class="data_list">
                        @foreach ($data_list as $key => $value)
                            <tr>
                                <th class="text-center">
                                    <input class="form-check-input single_select" type="checkbox" name="selected[{{ $key }}]" value="{{ $value->account_received_id }}">
                                    <input type="hidden" name="account_received_id[{{ $key }}]" class="select_input" value="{{ $value->account_received_id }}" disabled>

                                    <input type="hidden" name="amt_net[{{ $key }}]" class="select_input" value="{{ $value->tw_price }}" disabled>
                                </th>
                                <td>{{ $key + 1 }}</td>
                                <td><a href="{{ $value->link }}">{{ $value->ro_sn }}</a></td>
                                <td>{{ $value->ro_target_name }}</td>
                                <td>{{ $value->ro_received_grade_code }} {{ $value->ro_received_grade_name }}</td>
                                <td>{{ $value->summary }}</td>
                                <td>{{ number_format($value->tw_price) }}</td>
                                <td>{{ $value->account_status_code == 0 ? '未入款' : '已入款' }}</td>
                                <td>{{ $value->ro_created ? date('Y-m-d', strtotime($value->ro_created)) : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4 submit" disabled="disabled">確認</button>
            <a href="{{ route('cms.account_received.index') }}" class="btn btn-outline-primary px-4" role="button">
                返回上一頁
            </a>
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