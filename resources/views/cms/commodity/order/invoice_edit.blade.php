@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-4">編輯發票</h2>

    @if($errors->any())
        <div class="alert alert-danger mt-3">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
    @endif
    <form method="POST" action="{{ $form_action }}" class="form">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">自定訂單編號 <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="自定訂單編號僅允許英數字及_符號"></i> <span class="text-danger">*</span></label>
                    <input type="text" name="merchant_order_no" class="form-control @error('merchant_order_no') is-invalid @enderror" placeholder="請輸入自定訂單編號" aria-label="自定訂單編號" value="{{ old('merchant_order_no', $invoice->merchant_order_no) }}" required>
                    <div class="invalid-feedback">
                        @error('merchant_order_no')
                        {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="col-12 col-sm-6 mb-3 c_invoice_number{{ old('invoice_method') == 'print' ? '' : ($invoice->print_flag == 'Y' ? '' : ' d-none') }}">
                    <label class="form-label l_invoice_number">自定發票號碼</label>
                    <input type="text" name="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror" placeholder="請輸入自定發票號碼" aria-label="自定訂單編號" value="{{ old('invoice_number', $invoice->invoice_number) }}">
                    <div class="invalid-feedback">
                        @error('invoice_number')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">開立狀態</legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="status" value="1" type="radio" id="now" required {{ old('status', $invoice->status) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="now">立即開立</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="status" value="9" type="radio" id="postpone" required {{ old('status', $invoice->status) == 9 ? 'checked' : '' }}>
                            <label class="form-check-label" for="postpone">暫不開立</label>
                        </div>
                    </div>
                    <div class="invalid-feedback">
                        @error('status')
                        {{ $message }}
                        @enderror
                    </div>
                </fieldset>

                <div class="col-12 col-sm-6 mb-3 c_status{{ $invoice->status == 1 ? '' : ' d-none' }}">
                    <label class="form-label" for="merge_source">合併發票</label>
                    <select name="merge_source[]" id="merge_source" multiple hidden class="-select2 -multiple form-select @error('merge_source') is-invalid @enderror" data-placeholder="請選擇合併發票">
                        @foreach ($merge_source as $value)
                            <option value="{{ $value->id }}" @if (in_array($value->id, old('merge_source', $merge_source_selected))) selected @endif>{{ $value->sn }}</option>
                        @endforeach
                    </select>
                    @error('merge_source')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">發票種類 <span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="category" value="B2C" type="radio" id="2c" required {{ old('category', $invoice->category) == 'B2C' ? 'checked' : '' }}>
                            <label class="form-check-label" for="2c">個人</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="category" value="B2B" type="radio" id="2b" required {{ old('category', $invoice->category) == 'B2B' ? 'checked' : '' }}>
                            <label class="form-check-label" for="2b">公司．企業</label>
                        </div>
                    </div>
                    <div class="invalid-feedback">
                        @error('category')
                        {{ $message }}
                        @enderror
                    </div>
                </fieldset>

                <div class="col-12 col-sm-6 mb-3 c_category{{ $invoice->category == 'B2B' ? '' : ' d-none' }}">
                    <label class="form-label l_buyer_ubn">公司統編{!! $invoice->category == 'B2B' ? ' <span class="text-danger">*</span>' : '' !!}</label>
                    <input type="text" name="buyer_ubn" class="form-control @error('buyer_ubn') is-invalid @enderror" placeholder="請輸入公司統編" aria-label="公司統編" value="{{ old('buyer_ubn', $invoice->buyer_ubn) }}"{{ $invoice->category == 'B2B' ? ' required' : ' disabled' }}>
                    <div class="invalid-feedback">
                        @error('buyer_ubn')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">買受人名稱 <span class="text-danger">*</span></label>
                    <input type="text" name="buyer_name" class="form-control @error('buyer_name') is-invalid @enderror" placeholder="請輸入買受人名稱" aria-label="買受人名稱" value="{{ old('buyer_name', $invoice->buyer_name) }}" required>
                    <div class="invalid-feedback">
                        @error('buyer_name')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label l_buyer_email">買受人E-mail{!! $invoice->print_flag == 'N' ? ' <span class="text-danger">*</span>' : '' !!}</label>
                    <input type="email" name="buyer_email" class="form-control @error('buyer_email') is-invalid @enderror" placeholder="請輸入買受人E-mail" aria-label="買受人E-mail"
                           value="{{ old('buyer_email', $invoice->buyer_email) }}" {{ $invoice->print_flag == 'N' ? 'required' : '' }}>
                    <div class="invalid-feedback">
                        @error('buyer_email')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                {{--
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label l_buyer_address">買受人地址 <span class="text-danger">*</span></label>
                    <input type="text" name="buyer_address" class="form-control @error('buyer_address') is-invalid @enderror" placeholder="請輸入買受人地址" aria-label="買受人地址" value="{{ old('buyer_address', $order->ord_address) }}" required>
                    <div class="invalid-feedback">
                        @error('buyer_address')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                --}}
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label l_buyer_address">買受人地址</label>
                    <input type="text" name="buyer_address" class="form-control @error('buyer_address') is-invalid @enderror" placeholder="請輸入買受人地址" aria-label="買受人地址" value="{{ old('buyer_address', $invoice->buyer_address) }}">
                    <div class="invalid-feedback">
                        @error('buyer_address')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">發票方式 <span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1 @error('invoice_method') is-invalid @enderror">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="invoice_method" value="print" type="radio" id="print" required {{ old('invoice_method') == 'print' ? 'checked' : ( (!old('invoice_method') && $invoice->print_flag == 'Y') ? 'checked' : '') }}>
                            <label class="form-check-label" for="print">紙本發票(無載具、列印電子發票證明聯、手開複寫紙發票)</label>
                        </div>
                        {{--
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="invoice_method" value="give" type="radio" id="give" required {{ old('invoice_method') && old('invoice_method') == 'give' ? 'checked' : '' }}>
                            <label class="form-check-label" for="give">捐贈</label>
                        </div>
                        --}}
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="invoice_method" value="e_inv" type="radio" id="e_inv" required {{ old('invoice_method') == 'e_inv' ? 'checked' : ( (!old('invoice_method') && $invoice->print_flag == 'N') ? 'checked' : '') }}{{ $invoice->category == 'B2B' ? 'disabled' : '' }}>
                            <label class="form-check-label" for="e_inv">電子發票</label>
                        </div>
                    </div>
                    <div class="invalid-feedback">
                        @error('invoice_method')
                        {{ $message }}
                        @enderror
                    </div>
                </fieldset>

                <div class="col-12 col-sm-6 mb-3 c_invoice_method d-none">
                    <label class="form-label l_love_code" for="love_code">捐贈單位</label>
                    {{--
                    <select name="love_code" id="love_code" hidden class="-select2 -single form-select @error('love_code') is-invalid @enderror" data-placeholder="請選擇捐贈單位">
                        @foreach ($unit as $value)
                        <option value="{{ $value->code }}" @if ($value->code == old('love_code')) selected @endif>{{ $value->name }}</option>
                        @endforeach
                    </select>
                    --}}
                    @error('love_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row carrier{{ old('invoice_method') == 'e_inv' ? '' : ( (!old('invoice_method') && $invoice->print_flag == 'N') ? '' : ' d-none') }}">
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">載具類型 <span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="carrier_type" value="0" type="radio" id="carrier_mobile" {{ (old('carrier_type', $invoice->carrier_type) == 0) && old('carrier_type', $invoice->carrier_type) != null ? 'checked' : '' }}>
                            <label class="form-check-label" for="carrier_mobile">手機條碼載具</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="carrier_type" value="1" type="radio" id="carrier_certificate" {{ old('carrier_type', $invoice->carrier_type) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="carrier_certificate">自然人憑證條碼載具</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="carrier_type" value="2" type="radio" id="carrier_member" {{ old('carrier_type', $invoice->carrier_type) == 2 ? 'checked' : '' }}>
                            <label class="form-check-label" for="carrier_member">會員載具</label>
                        </div>
                    </div>
                    <div class="invalid-feedback">
                        @error('carrier_type')
                        {{ $message }}
                        @enderror
                    </div>
                </fieldset>

                <div class="col-12 col-sm-6 mb-3 c_carrier_type{{ old('invoice_method') == 'e_inv' ? '' : ( (!old('invoice_method') && $invoice->print_flag == 'N') ? '' : ' d-none') }}">
                    <label class="form-label l_carrier_num">載具號碼{!! old('invoice_method') == 'e_inv' ? ' <span class="text-danger">*</span>' : ( (!old('invoice_method') && $invoice->print_flag == 'N') ? ' <span class="text-danger">*</span>' : '') !!}</label>
                    <input type="text" name="carrier_num" class="form-control @error('carrier_num') is-invalid @enderror" placeholder="請輸入載具號碼" aria-label="載具號碼" value="{{ old('carrier_num', $invoice->carrier_num) }}" {{ old('invoice_method') == 'e_inv' ? '' : ( (!old('invoice_method') && $invoice->print_flag == 'N') ? '' : 'disabled') }}>
                    <div class="invalid-feedback">
                        @error('carrier_num')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>

            {{--
                <div class="row">
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">預計開立日期</label>
                        <input type="date" name="create_status_time" class="form-control @error('create_status_time') is-invalid @enderror" placeholder="請輸入預計開立日期" aria-label="預計開立日期" value="{{ old('create_status_time', date('Y-m-d', strtotime( date('Y-m-d'))) ) }}">
                        <div class="invalid-feedback">
                            @error('create_status_time')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
            --}}
        </div>

        <div class="card shadow p-4 mb-4">
            <h6>發票明細</h6>
            {{--
            <div class="list-wrap-1">
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle d-sm-table d-none text-nowrap">
                        <tbody class="border-top-0 m_row">
                        <tr class="table-light">
                            <td class="col-2" style="display:none">收款單號</td>
                            <td class="col-2">產品名稱</td>
                            <td class="col-2">價格(總價)</td>
                            <td class="col-2">數量</td>
                            <td class="col-2">說明</td>
                            <td class="col-2">稅別</td>
                        </tr>
                        @foreach($sub_order as $s_value)
                            @foreach($s_value->items as $value)
                                <tr>
                                    <td style="display:none">{{ $received_order->sn }}</td>
                                    <td>{{ $value->product_title }}</td>
                                    <td>{{ number_format($value->total_price) }}</td>
                                    <td>{{ $value->qty }}</td>
                                    <td></td>
                                    <td>{{ $value->product_taxation == 1 ? '應稅' : '免稅'}}</td>
                                </tr>
                            @endforeach
                        @endforeach

                        @if($order->dlv_fee > 0)
                            <tr>
                                <td style="display:none">{{ $received_order->sn }}</td>
                                <td>物流費用</td>
                                <td>{{ number_format($order->dlv_fee) }}</td>
                                <td>1</td>
                                <td></td>
                                <td>{{ $order->dlv_taxation == 1 ? '應稅' : '免稅'}}</td>
                            </tr>
                        @endif

                        @if(count($order_discount) > 0)
                            @foreach($order_discount as $value)
                                <tr>
                                    <td style="display:none">{{ $received_order->sn }}</td>
                                    <td>{{ $value->title }}</td>
                                    <td>-{{ number_format($value->discount_value) }}</td>
                                    <td>1</td>
                                    <td></td>
                                    <td>{{ $value->discount_taxation == 1 ? '應稅' : '免稅'}}</td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
            --}}
            <div class="list-wrap-2">
                <div class="table-responsive tableOverBox">
                    <table class="table tableList table-hover mb-1">
                        <thead>
                            <tr>
                                <th scope="col" class="text-center">刪除</th>
                                <th class="col" style="display:none">收款單號</th>
                                <th class="col">產品名稱 <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="商品名稱至多為30個字元"></i></th>
                                <th class="col">單價</th>
                                <th class="col">數量</th>
                                <th class="col">總價金額</th>
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
                                <td style="display:none"></td>
                                <td>
                                    <input type="text" name="o_title[]" class="form-control form-control-sm -xl" value="" aria-label="產品名稱" minlength="1" maxlength="30" required>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" name="o_price[]" class="form-control form-control-sm -sm" value="" aria-label="價格(單價)" required>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" name="o_qty[]" class="form-control form-control-sm -sm" value="1" aria-label="數量" min="1" required>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" name="o_total_price[]" class="form-control form-control-sm -sm" value="" aria-label="價格(總價)" required>
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
                                        <td style="display:none"></td>
                                        <td><input type="text" name="o_title[]" class="form-control form-control-sm -xl" value="{{ old('o_title.' . $key) }}" aria-label="產品名稱" minlength="1" maxlength="30" required>
                                        <td>
                                            <div class="input-group input-group-sm flex-nowrap">
                                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                                <input type="number" name="o_price[]" class="form-control form-control-sm -sm" value="{{ old('o_price.' . $key) }}" aria-label="價格(單價)" required>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="number" name="o_qty[]" class="form-control form-control-sm -sm" value="{{ old('o_qty.' . $key) }}" aria-label="數量" min="1" required>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm flex-nowrap">
                                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                                <input type="number" name="o_total_price[]" class="form-control form-control-sm -sm" value="{{ old('o_total_price.' . $key) }}" aria-label="價格(總價)" required>
                                            </div>
                                        </td>
                                        <td>
                                            <select name="o_taxation[]" class="form-select form-select-sm" required>
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
                                    $name_arr = explode('|', $invoice->item_name);
                                    $count_arr = explode('|', $invoice->item_count);
                                    $price_arr = explode('|', $invoice->item_price);
                                    $amt_arr = explode('|', $invoice->item_amt);
                                    $tax_type_arr = explode('|', $invoice->item_tax_type);
                                @endphp

                                @foreach($name_arr as $key => $value)
                                    <tr class="-cloneElem">
                                        <td class="text-center">
                                            <button type="button" class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                        <td style="display:none"></td>
                                        <td><input type="text" name="o_title[]" class="form-control form-control-sm -xl" value="{{ mb_substr(preg_replace('/(\t|\r|\n|\r\n)+/', ' ', $value), 0, 30) }}" aria-label="產品名稱" minlength="1" maxlength="30" required>
                                        <td>
                                            <div class="input-group input-group-sm flex-nowrap">
                                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                                <input type="number" name="o_price[]" class="form-control form-control-sm -sm" value="{{ $price_arr[$key] }}" aria-label="價格(單價)" required>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="number" name="o_qty[]" class="form-control form-control-sm -sm" value="{{ $count_arr[$key] }}" aria-label="數量" min="1" required>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm flex-nowrap">
                                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                                <input type="number" name="o_total_price[]" class="form-control form-control-sm -sm" value="{{ $amt_arr[$key] }}" aria-label="價格(總價)" required>
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
            <a href="{{ Route('cms.order.detail', ['id' => $invoice->source_id]) }}" class="btn btn-outline-primary px-4" role="button">
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
                //開立狀態
                $('input[type=radio][name=status]').on('click change', function() {
                    if (this.value == 9) {
                        $('.c_status').addClass('d-none');
                        $('#merge_source').prop('disabled', true);

                        //     $('.list-wrap-1').removeClass('d-none');
                        //     $('.list-wrap-2').addClass('d-none').find('input, select, button').prop({
                        //         disabled:true,
                        //         required:false
                        //     });

                    } else {
                        $('.c_status').removeClass('d-none');
                        $('#merge_source').prop('disabled', false);

                        //     $('.list-wrap-1').addClass('d-none');
                        //     $('.list-wrap-2').removeClass('d-none').find('input, select, button').prop({
                        //         disabled:false,
                        //         required:true
                        //     });
                    }
                });

                //合併狀態
                $('#merge_source').on('change', function() {
                    // item.value is order_id
                    // item.text is order_sn
                    // .map(function(){
                    //     return this.value
                    // }).get().join(',')

                    const _URL = @json(route('cms.order.ajax-detail'));
                    let Data = {
                        order_id: $('#merge_source option:selected').toArray().map(item => item.value).join()
                    };

                    if (Data.order_id && Data.order_id != '') {
                        axios.post(_URL, Data)
                            .then((result) => {
                                const res = result.data;
                                $('tr.new_row').remove();
                                if (res && res.length) {
                                    (res).forEach(data => {
                                        $('.m_row').append(
                                            `<tr class="-cloneElem new_row">
                                                <td class="text-center">
                                                    <button type="button" class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>

                                                <td style="display:none">${data.received_sn}</td>

                                                <td><input type="text" name="o_title[]" class="form-control form-control-sm -l" value="${data.name.substring(0, 30)}" aria-label="產品名稱" minlength="1" maxlength="30" required>
                                                <td>
                                                    <div class="input-group input-group-sm flex-nowrap">
                                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                                        <input type="number" name="o_price[]" class="form-control form-control-sm -l" value="${data.price}" aria-label="價格(單價)" required>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="number" name="o_qty[]" class="form-control form-control-sm -l" value="${data.count}" aria-label="數量" min="1" required>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm flex-nowrap">
                                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                                        <input type="number" name="o_total_price[]" class="form-control form-control-sm -l" value="${data.amt}" aria-label="價格(總價)" required>
                                                    </div>
                                                </td>
                                                <td>
                                                    <select name="o_taxation[]" class="form-select form-select-sm" required>
                                                    <option value="1"${ data.tax == 1 ? ' selected' : '' }>應稅</option>
                                                    <option value="0"${ data.tax == 0 ? ' selected' : '' }>免稅</option>
                                                    </select>
                                                </td>
                                            </tr>`
                                        );
                                    });

                                    // del
                                    Clone_bindDelElem($('.-del'));
                                }

                            }).catch((err) => {
                            console.error(err);
                        });
                    } else if(!Data.order_id && Data.order_id == ''){
                        $('tr.new_row').remove();
                    }
                });


                //發票種類
                $('input[type=radio][name=category]').on('click change', function() {
                    if (this.value == 'B2C') {
                        $('.c_category').addClass('d-none');
                        $('.l_buyer_ubn').html('公司統編');
                        $('input[type=text][name=buyer_ubn]').prop({
                            disabled:true,
                            required:false
                        }).val('');

                        $('input:radio[name=invoice_method][value!="print"]').prop({
                            disabled:false,
                        });

                    } else if (this.value == 'B2B') {
                        $('.c_category').removeClass('d-none');
                        $('.l_buyer_ubn').html('公司統編 <span class="text-danger">*</span>');
                        $('input[type=text][name=buyer_ubn]').prop({
                            disabled:false,
                            required:true
                        });

                        $('input:radio[name=invoice_method][value="print"]').click();
                        $('input:radio[name=invoice_method][value!="print"]').prop({
                            disabled:true,
                        });
                    }
                });

                //發票方式
                $('input[type=radio][name=invoice_method]').on('click change', function() {
                    if (this.value == 'print') {
                        //Email
                        $('input[type=email][name=buyer_email]').prop({
                            required:false
                        });
                        $('.l_buyer_email').html('買受人E-mail');

                        //地址
                        // $('input[type=text][name=buyer_address]').prop({
                        //     required:true
                        // });
                        // $('.l_buyer_address').html('買受人地址 <span class="text-danger">*</span>');

                        //捐贈
                        $('.c_invoice_method').addClass('d-none');
                        $('.l_love_code').html('捐贈單位');
                        $('#love_code').prop({
                            disabled:true,
                            required:false
                        }).val('');

                        //載具
                        $('.carrier').addClass('d-none');
                        $('input[type=radio][name=carrier_type]').prop({
                            disabled:true,
                            required:false,
                            checked:false
                        });
                        $('.c_carrier_type').addClass('d-none');
                        $('input[type=text][name=carrier_num]').prop({
                            disabled:true,
                            required:false
                        }).val('');
                        $('.l_carrier_num').html('載具號碼');

                        //自定發票號碼
                        $('.c_invoice_number').removeClass('d-none');
                        $('input[type=text][name=invoice_number]').prop({
                            disabled:false,
                        });

                    } else if(this.value == 'give'){
                        //Email
                        $('input[type=email][name=buyer_email]').prop({
                            required:false
                        });
                        $('.l_buyer_email').html('買受人E-mail');

                        //地址
                        // $('input[type=text][name=buyer_address]').prop({
                        //     required:false
                        // });
                        // $('.l_buyer_address').html('買受人地址');

                        //捐贈
                        $('.c_invoice_method').removeClass('d-none');
                        $('.l_love_code').html('捐贈單位 <span class="text-danger">*</span>');
                        $('#love_code').prop({
                            disabled:false,
                            required:true
                        });

                        //載具
                        $('.carrier').addClass('d-none');
                        $('input[type=radio][name=carrier_type]').prop({
                            disabled:true,
                            required:false,
                            checked:false
                        });
                        $('.c_carrier_type').addClass('d-none');
                        $('input[type=text][name=carrier_num]').prop({
                            disabled:true,
                            required:false
                        }).val('');
                        $('.l_carrier_num').html('載具號碼');

                        //自定發票號碼
                        $('.c_invoice_number').addClass('d-none');
                        $('input[type=text][name=invoice_number]').prop({
                            disabled:true,
                        });

                    } else if(this.value == 'e_inv'){
                        //地址
                        // $('input[type=text][name=buyer_address]').prop({
                        //     required:false
                        // });
                        // $('.l_buyer_address').html('買受人地址');

                        //Email
                        $('input[type=email][name=buyer_email]').prop({
                            required:true
                        });
                        $('.l_buyer_email').html('買受人E-mail <span class="text-danger">*</span>');

                        //捐贈
                        $('.c_invoice_method').addClass('d-none');
                        $('.l_love_code').html('捐贈單位');
                        $('#love_code').prop({
                            disabled:true,
                            required:false
                        }).val('');

                        //載具
                        $('.carrier').removeClass('d-none');
                        $('input[type=radio][name=carrier_type]').prop({
                            disabled:false,
                            required:true
                        });

                        //載具號碼
                        $('.c_carrier_type').removeClass('d-none');
                        $('input[type=text][name=carrier_num]').prop({
                            disabled:false,
                            required:true
                        });
                        $('.l_carrier_num').html('載具號碼 <span class="text-danger">*</span>');

                        //自定發票號碼
                        $('.c_invoice_number').addClass('d-none');
                        $('input[type=text][name=invoice_number]').prop({
                            disabled:true,
                        });
                    }

                });

                //載具類型
                $('input[type=radio][name=carrier_type]').on('click change', function() {
                    if (this.value == 2) {
                        $('input[type=email][name=buyer_email]').prop({
                            required:true
                        });
                        $('.l_buyer_email').html('買受人E-mail <span class="text-danger">*</span>');

                    } else {

                        $('input[type=email][name=buyer_email]').prop({
                            required:false
                        });
                        $('.l_buyer_email').html('買受人E-mail');
                    }

                    $('.c_carrier_type').removeClass('d-none');
                    $('input[type=text][name=carrier_num]').prop({
                        disabled:false,
                        required:true
                    });
                    $('.l_carrier_num').html('載具號碼 <span class="text-danger">*</span>');
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
