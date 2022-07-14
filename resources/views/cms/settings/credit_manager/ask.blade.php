@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">信用卡整批請款</h2>

    <a href="{{ route('cms.credit_manager.index') }}" class="btn btn-primary" role="button">
        <i class="bi bi-arrow-left"></i> 返回上一頁
    </a>

    <form method="POST" action="{{ $form_action }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            @if($errors->any())
            <div class="alert alert-danger">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
            @endif
            <div class="table-responsive tableOverBox">
                <table class="table table-hover tableList mb-1">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col" class="text-center"><input class="form-check-input" type="checkbox" id="checkAll"></th>
                            <th scope="col">編號</th>
                            <th scope="col">信用卡號</th>
                            <th scope="col">刷卡金額</th>
                            <th scope="col">狀態</th>
                            <th scope="col">刷卡日期</th>
                            <th scope="col">卡別</th>
                            <th scope="col">收款單號</th>
                            <th scope="col">請款銀行</th>
                        </tr>
                    </thead>

                    <tbody class="card_list">
                        @foreach ($data_list as $key => $value)
                            <tr>
                                <th class="text-center">
                                    <input class="form-check-input single_select" type="checkbox" name="selected[{{ $key }}]" value="{{ $value->credit_card_received_id }}">
                                    <input type="hidden" name="credit_card_received_id[{{ $key }}]" class="select_input" value="{{ $value->credit_card_received_id }}" disabled>
                                </th>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $value->credit_card_number }}</td>
                                <td>{{ number_format($value->credit_card_price) }}</td>
                                <td>{{ $value->credit_card_status_code == 0 ? '刷卡' : ($value->credit_card_status_code == 1 ? '請款' : '入款') }}</td>
                                <td>{{ date('Y-m-d', strtotime($value->credit_card_checkout_date)) }}</td>
                                <td>{{ $value->credit_card_type }}</td>
                                <td>
                                    @if($value->ro_source_type == 'ord_orders')
                                    <a href="{{ route('cms.collection_received.receipt', ['id' => $value->ro_source_id]) }}" class="-text">{{ $value->ro_sn }}</a>
                                    @elseif($value->ro_source_type == 'csn_orders')
                                    <a href="{{ route('cms.ar_csnorder.receipt', ['id' => $value->ro_source_id]) }}" class="-text">{{ $value->ro_sn }}</a>
                                    @endif
                                </td>
                                <td>{{ $value->bank_name}}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <th></th>
                            <td></td>
                            <td>合計張數：{{ count($data_list) }} 張</td>
                            <td>合計金額：{{ number_format($data_list->sum('credit_card_price')) }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-12">
                    <label class="form-label">請款日期 <span class="text-danger">*</span></label>
                    <input type="date" name="transaction_date" class="form-control @error('transaction_date') is-invalid @enderror" placeholder="請輸入請款日期" aria-label="請款日期" value="{{ old('transaction_date', date('Y-m-d', strtotime( date('Y-m-d'))) ) }}" required>
                    <div class="invalid-feedback">
                        @error('transaction_date')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
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
                    $('.card_list tr').each(function( index ) {
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

                // $('#keyword').on('keyup', function () {
                //     let keyword = $(this).val().toLowerCase();
                //     $('.card_list tr').filter(function () {
                //         $(this).toggle($(this).children('td:eq(0)').text().toLowerCase().indexOf(keyword) > -1 || $(this).children('td:eq(2)').text().toLowerCase().indexOf(keyword) > -1)
                //     });
                // });

                // $('.reset').on('click', function(){
                //     $('#keyword').val('');
                //     $('.card_list tr').css('display', '');
                // });
            });
        </script>
    @endpush
@endonce