@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-4">Line Pay 付款取消</h2>

    @if($errors->any())
        <div class="alert alert-danger mt-3">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
    @endif
    <form method="POST" action="{{ $form_action }}" class="form">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <tbody class="border-top-0">
                            <tr class="table-light text-center">
                                <td colspan="4">Line Pay 付款取消</td>
                            </tr>
                            <tr>
                                <th class="table-light" style="width:30%">起初核準的金額</th>
                                <td style="width:70%">{{ number_format($pay_in) }}</td>
                            </tr>
                            <tr>
                                <th class="table-light" style="width:30%">允許的取消金額</th>
                                <td style="width:70%" class="balance">{{ number_format($balance) }}</td>
                            </tr>
                            <tr>
                                <th class="table-light" style="width:30%">請求的部分取消金額 <span class="text-danger">*</span></th>
                                <td style="width:70%">
                                    <div class="input-group input-group flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" name="pay_out_price" class="form-control pay_out_price @error('pay_out_price') is-invalid @enderror" placeholder="請輸入取消金額" aria-label="取消金額" value="{{ old('pay_out_price', ($balance)) }}" min="1" max="{{ $balance }}" required>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="table-light" style="width:30%">取消後餘額</th>
                                <td style="width:70%" class="refund_result">0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">確認</button>
            <a href="{{ Route('cms.order.detail', ['id' => $order->id]) }}" class="btn btn-outline-primary px-4" role="button">
                返回明細
            </a>
        </div>
    </form>
@endsection

@once
    @push('sub-styles')

    @endpush
    @push('sub-scripts')
        <script>
            $(function() {
                $('.pay_out_price').on('change', function(e) {
                    let result = 0;
                    let balance = parseFloat($('.balance').text().replace(/\,/g,''));
                    result = balance - $(this).val();
                    $('.refund_result').text(result.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,"));
                });

                $('.form').submit((e) => {
                    $('#err_msg').remove();

                    if ($('.list-wrap-2 input[name^="o_title"]').length < 1) {
                        $('div.list-wrap-2').append('<div id="err_msg" class="alert alert-danger mt-3">項目不可空白</div>');
                        return false;
                    }

                    let sum = 0;
                    $('input[name^="o_total_price"]').each(function(){
                        sum += (+$(this).val());
                    });

                    if (sum <= 0) {
                        $('div.list-wrap-2').append('<div id="err_msg" class="alert alert-danger mt-3">總價合計不可小於1</div>');
                        return false;
                    }
                });
            });
        </script>
    @endpush
@endonce
