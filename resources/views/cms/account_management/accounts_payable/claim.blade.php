@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-4">應付帳款入款</h2>

    <a href="{{ route('cms.accounts_payable.index') }}" class="btn btn-primary" role="button">
        <i class="bi bi-arrow-left"></i> 返回上一頁
    </a>

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
                            <th scope="col">編號</th>
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
                                    <input class="form-check-input single_select" type="checkbox" name="selected[{{ $key }}]" value="{{ $value->account_payable_id }}">
                                    <input type="hidden" name="accounts_payable_id[{{ $key }}]" class="select_input" value="{{ $value->account_payable_id }}" disabled>

                                    <input type="hidden" name="amt_net[{{ $key }}]" class="select_input" value="{{ $value->tw_price }}" disabled>
                                </th>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    @php
                                        if($value->po_source_type == 'pcs_purchase'){
                                            $url_link = route('cms.purchase.view-pay-order', ['id' => $value->po_source_id, 'type' => $value->po_type]);

                                        } else if($value->po_source_type == 'ord_orders' && $value->po_source_sub_id != null){
                                            $url_link = route('cms.order.logistic-po', ['id' => $value->po_source_id, 'sid' => $value->po_source_sub_id]);

                                        } else if($value->po_source_type == 'acc_stitute_orders'){
                                            $url_link = route('cms.stitute.po-show', ['id' => $value->po_source_id]);

                                        } else if($value->po_source_type == 'ord_orders' && $value->po_source_sub_id == null){
                                            $url_link = route('cms.order.return-pay-order', ['id' => $value->po_source_id]);

                                        } else if($value->po_source_type == 'dlv_delivery'){
                                            $url_link = route('cms.delivery.return-pay-order', ['id' => $value->po_source_id]);

                                        } else if($value->po_source_type == 'pcs_paying_orders'){
                                            $url_link = route('cms.accounts_payable.po-show', ['id' => $data->po_source_id]);

                                        } else {
                                            $url_link = "javascript:void(0);";
                                        }
                                    @endphp
                                    <a href="{{ $url_link }}">{{ $value->po_sn }}</a>
                                </td>
                                <td>{{ $value->po_target_name }}</td>
                                <td>{{ $value->po_payable_grade_code }} {{ $value->po_payable_grade_name }}</td>
                                <td>{{ $value->summary }}</td>
                                <td>{{ number_format($value->tw_price) }}</td>
                                <td>{{ $value->account_status_code == 0 ? '未入款' : '已入款' }}</td>
                                <td>{{ $value->po_created ? date('Y-m-d', strtotime($value->po_created)) : '' }}</td>
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