@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">整批{{ $type == 'collection' ? '託收' : ($type == 'nd' ? '次交票' : '兌現') }}</h2>

    <a href="{{ $previous_url }}" class="btn btn-primary" role="button">
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
                            <th scope="col">支票號碼</th>
                            <th scope="col">金額</th>
                            <th scope="col">狀態</th>
                            <th scope="col">收款單號</th>
                            <th scope="col">收票日期</th>
                            <th scope="col">託收/次交日期</th>
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

                    <tbody class="card_list">
                        @foreach ($data_list as $key => $value)
                            <tr>
                                <th class="text-center">
                                    @if($value->cheque_status_code != 'cashed')
                                    <input class="form-check-input single_select" type="checkbox" name="selected[{{ $key }}]" value="{{ $value->cheque_received_id }}">
                                    <input type="hidden" name="cheque_received_id[{{ $key }}]" class="select_input" value="{{ $value->cheque_received_id }}" disabled>

                                    <input type="hidden" name="amt_net[{{ $key }}]" class="select_input" value="{{ $value->tw_price }}" disabled>
                                    @endif
                                </th>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $value->cheque_ticket_number }}</td>
                                <td>{{ number_format($value->tw_price) }}</td>
                                <td>{{ $value->cheque_status }}</td>
                                <td>{{ $value->ro_sn }}</td>
                                <td>{{ $value->ro_receipt_date ? date('Y-m-d', strtotime($value->ro_receipt_date)) : '' }}</td>
                                <td>{{ $value->cheque_c_n_date ? date('Y-m-d', strtotime($value->cheque_c_n_date)) : '' }}</td>
                                <td>{{ $value->cheque_due_date ? date('Y-m-d', strtotime($value->cheque_due_date)) : '' }}</td>
                                <td>{{ $value->cheque_cashing_date ? date('Y-m-d', strtotime($value->cheque_cashing_date)) : '' }}</td>
                                <td>{{ $value->cheque_draw_date ? date('Y-m-d', strtotime($value->cheque_draw_date)) : '' }}</td>
                                <td>{{ $value->ro_undertaker }}</td>
                                <td>{{ $value->cheque_drawer }}</td>
                                <td>{{ $value->cheque_banks }}</td>
                                <td>{{ $value->cheque_accounts }}</td>
                                <td></td>
                                <td>{{ $value->cheque_deposited_area }}</td>
                                <td>{{ $value->note}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-11">
                    @if ($type == 'collection')
                    <label class="form-label">託收日期 <span class="text-danger">*</span></label>
                    <input type="date" name="c_n_date" class="form-control @error('c_n_date') is-invalid @enderror" placeholder="請輸入託收日期" aria-label="託收日期" value="{{ old('c_n_date', date('Y-m-d', strtotime( date('Y-m-d'))) ) }}" required>
                    @elseif ($type == 'nd')
                    <label class="form-label">次交日期 <span class="text-danger">*</span></label>
                    <input type="date" name="c_n_date" class="form-control @error('c_n_date') is-invalid @enderror" placeholder="請輸入次交日期" aria-label="次交日期" value="{{ old('c_n_date', date('Y-m-d', strtotime( date('Y-m-d'))) ) }}" required>
                    @elseif ($type == 'cashed')
                    <label class="form-label">兌現日期 <span class="text-danger">*</span></label>
                    <input type="date" name="cashing_date" class="form-control @error('cashing_date') is-invalid @enderror" placeholder="請輸入兌現日期" aria-label="兌現日期" value="{{ old('cashing_date', date('Y-m-d', strtotime( date('Y-m-d'))) ) }}" required>
                    @endif
                    <div class="invalid-feedback">
                        @error('c_n_date' || 'cashing_date')
                        {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="col-1 align-self-end">
                    <button type="button" class="btn btn-primary px-4 query_date">查詢</button>
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
                });

                $('.single_select').click(function(){
                    $('.submit').prop('disabled', $('input.single_select:checked').length == 0);
                    $(this).parents('tr').find('input.select_input').prop('disabled', !this.checked);
                });

                $('.query_date').click(function(){
                    const q_date = $('input[name="c_n_date"]').val() || $('input[name="cashing_date"]').val();
                    const url = '{{ route('cms.note_receivable.detail', ['type'=>$type]) }}' + '?qd=' + q_date;
                    window.location.href = url;
                });
            });
        </script>
    @endpush
@endonce