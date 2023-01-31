@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-4">編輯發票折讓</h2>

    @if($errors->any())
        <div class="alert alert-danger mt-3">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
    @endif

    <form method="POST" action="{{ $form_action }}" class="form">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <tbody class="border-top-0">
                        <tr class="table-light text-center">
                            <td colspan="4">折讓明細</td>
                        </tr>
                        <tr>
                            <th class="table-light" style="width:15%">自定訂單編號</th>
                            <td style="width:35%">{{ $invoice->merchant_order_no }}</td>
                            <th class="table-light" style="width:15%">自定發票號碼</th>
                            <td style="width:35%">{{ $invoice->invoice_number }}</td>
                        </tr>
                        <tr>
                            <th class="table-light" style="width:15%">買受人E-mail</th>
                            <td style="width:35%"><input type="email" name="buyer_email" class="form-control @error('buyer_email') is-invalid @enderror" placeholder="請輸入買受人E-mail" aria-label="買受人E-mail" value="{{ old('buyer_email', $inv_allowance->buyer_email) }}"></td>
                            <th class="table-light" style="width:15%">發票可折讓餘額</th>
                            <td style="width:35%" class="inv_remain">{{ number_format($inv_remain) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow p-4 mb-4">
            <h6>折讓商品明細</h6>

            <div class="list-wrap-2">
                <div class="table-responsive tableOverBox">
                    <table class="table tableList table-hover mb-1">
                        <thead>
                            <tr>
                                <th scope="col" class="text-center">刪除</th>
                                <th class="col">產品名稱 <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="商品名稱至多為30個字元"></i></th>
                                <th class="col">單價</th>
                                <th class="col">數量</th>
                                <th class="col">總價金額</th>
                                <th class="col">營業稅額</th>
                                <th class="col">稅別</th>
                            </tr>
                        </thead>

                        <tbody class="-appendClone m_row">
                            <tr class="-cloneElem d-none">
                                <td class="text-center">
                                    <button type="button" class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                                <td>
                                    <input type="text" name="o_title[]" class="form-control form-control-sm -xl" value="" aria-label="產品名稱" minlength="1" maxlength="30" required>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" name="o_price[]" class="form-control form-control-sm -sm" value="" aria-label="價格(單價)" min="0" required>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" name="o_qty[]" class="form-control form-control-sm -sm" value="1" aria-label="數量" min="1" required>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" name="o_total_price[]" class="form-control form-control-sm -sm" value="" aria-label="價格(總價)" min="0" required>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" name="o_tax_price[]" class="form-control form-control-sm -sm" value="" aria-label="稅額" min="0" required>
                                    </div>
                                </td>
                                <td>
                                    <select name="o_taxation[]" class="form-select form-select-sm" required>
                                        @php
                                            $tax = [
                                                1 => '應稅',
                                                0 => '免稅',
                                            ];

                                            foreach($tax as $t_key => $t_value){
                                                echo '<option value="' . $t_key . '"' . ($t_key == 1 ? ' selected' : '') . '>' . $t_value . '</option>';
                                            }
                                        @endphp
                                    </select>
                                </td>
                            </tr>

                            @if(old('o_title'))
                                @foreach(old('o_title') as $key => $value)
                                    <tr class="-cloneElem">
                                        <td class="text-center">
                                            <button type="button" class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                        <td><input type="text" name="o_title[]" class="form-control form-control-sm -xl @error('o_title.' . $key) is-invalid @enderror" value="{{ old('o_title.' . $key) }}" aria-label="產品名稱" minlength="1" maxlength="30" required>
                                        <td>
                                            <div class="input-group input-group-sm flex-nowrap">
                                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                                <input type="number" name="o_price[]" class="form-control form-control-sm -sm @error('o_price.' . $key) is-invalid @enderror" value="{{ old('o_price.' . $key) }}" aria-label="價格(單價)" min="0" required>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="number" name="o_qty[]" class="form-control form-control-sm -sm @error('o_qty.' . $key) is-invalid @enderror" value="{{ old('o_qty.' . $key) }}" aria-label="數量" min="1" required>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm flex-nowrap">
                                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                                <input type="number" name="o_total_price[]" class="form-control form-control-sm -sm @error('o_total_price.' . $key) is-invalid @enderror" value="{{ old('o_total_price.' . $key) }}" aria-label="價格(總價)" min="0" required>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm flex-nowrap">
                                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                                <input type="number" name="o_tax_price[]" class="form-control form-control-sm -sm @error('o_tax_price.' . $key) is-invalid @enderror" value="{{ old('o_tax_price.' . $key) }}" aria-label="稅額" min="0" required>
                                            </div>
                                        </td>
                                        <td>
                                            <select name="o_taxation[]" class="form-select form-select-sm @error('o_taxation.' . $key) is-invalid @enderror" required>
                                                @php
                                                    foreach($tax as $t_key => $t_value){
                                                        echo '<option value="' . $t_key . '"' . ($t_key == old('o_taxation.' . $key) ? ' selected' : '') . '>' . $t_value . '</option>';
                                                    }
                                                @endphp
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                @php
                                    $name_arr = explode('|', $inv_allowance->item_name);
                                    $count_arr = explode('|', $inv_allowance->item_count);
                                    $price_arr = explode('|', $inv_allowance->item_price);
                                    $amt_arr = explode('|', $inv_allowance->item_amt);
                                    $tax_amt_arr = explode('|', $inv_allowance->item_tax_amt);
                                    $tax_type_arr = explode('|', $inv_allowance->item_tax_type);
                                @endphp

                                @foreach($name_arr as $key => $value)
                                    <tr class="-cloneElem">
                                        <td class="text-center">
                                            <button type="button" class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                        <td><input type="text" name="o_title[]" class="form-control form-control-sm -xl" value="{{ mb_substr(preg_replace('/(\t|\r|\n|\r\n)+/', ' ', $value), 0, 30) }}" aria-label="產品名稱" minlength="1" maxlength="30" required>
                                        <td>
                                            <div class="input-group input-group-sm flex-nowrap">
                                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                                <input type="number" name="o_price[]" class="form-control form-control-sm -sm" value="{{ $price_arr[$key] }}" aria-label="價格(單價)" min="0" required>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="number" name="o_qty[]" class="form-control form-control-sm -sm" value="{{ $count_arr[$key] }}" aria-label="數量" min="1" required>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm flex-nowrap">
                                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                                <input type="number" name="o_total_price[]" class="form-control form-control-sm -sm" value="{{ $amt_arr[$key] }}" aria-label="價格(總價)" min="0" required>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm flex-nowrap">
                                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                                <input type="number" name="o_tax_price[]" class="form-control form-control-sm -sm" value="{{ $tax_amt_arr[$key] }}" aria-label="稅額" min="0" required>
                                            </div>
                                        </td>
                                        <td>
                                            <select name="o_taxation[]" class="form-select form-select-sm" required>
                                                @php
                                                    foreach($tax as $t_key => $t_value){
                                                        echo '<option value="' . $t_key . '"' . ($t_key == ($tax_type_arr[$key] == 1 ? 1 : 0) ? ' selected' : '') . '>' . $t_value . '</option>';
                                                    }
                                                @endphp
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="d-grid gap-2 mt-3">
                    <button type="button" class="btn btn-outline-primary border-dashed -newClone" style="font-weight: 500;">
                        <i class="bi bi-plus-circle"></i> 新增項目
                    </button>
                </div>
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">確認</button>
            <a href="{{ $previous_url }}" class="btn btn-outline-primary px-4" role="button">
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
            const $clone = $('.-cloneElem:first-child').clone();
            $('.-cloneElem.d-none').remove();

            // 新增
            $('.-newClone').off('click').on('click', function() {
                Clone_bindCloneBtn($clone, function() {});
            });

            // del
            Clone_bindDelElem($('.-del'));

            $(function() {
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

                    if (sum < 0) {
                        $('div.list-wrap-2').append('<div id="err_msg" class="alert alert-danger mt-3">總價合計不可小於0</div>');
                        return false;
                    }

                    let tax_sum = 0;
                    $('input[name^="o_tax_price"]').each(function(){
                        tax_sum += (+$(this).val());
                    });

                    if (tax_sum < 0) {
                        $('div.list-wrap-2').append('<div id="err_msg" class="alert alert-danger mt-3">營業稅額合計不可小於0</div>');
                        return false;
                    }

                    let same_tax = true;
                    let o_taxation = '';
                    $('select[name^="o_taxation"]').each(function(){
                        if (o_taxation && o_taxation !== this.value) {
                            same_tax = false;
                            return;
                        }

                        o_taxation = this.value;
                    });
                    if (same_tax == false) {
                        $('div.list-wrap-2').append('<div id="err_msg" class="alert alert-danger mt-3">折讓商品稅別不可為混合課稅</div>');
                        return false;
                    }

                    let remain = parseFloat($('.inv_remain').text().replace(/,/g, ''));
                    if (remain - (sum + tax_sum) < 0) {
                        $('div.list-wrap-2').append('<div id="err_msg" class="alert alert-danger mt-3">折讓商品總價和稅別不可大於發票可折讓餘額</div>');
                        return false;
                    }
                });
            });
        </script>
    @endpush
@endonce
