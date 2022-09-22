@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-4">開立電子發票</h2>

    <form method="POST" action="{{ $form_action }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="row">
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">開立狀態</legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="status" value="1" type="radio" id="now" required {{ ! old('status') || old('status') == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="now">立即開立</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="status" value="9" type="radio" id="postpone" required {{ old('status') && old('status') == 9 ? 'checked' : '' }}>
                            <label class="form-check-label" for="postpone">暫不開立</label>
                        </div>
                    </div>
                    <div class="invalid-feedback">
                        @error('status')
                        {{ $message }}
                        @enderror
                    </div>
                </fieldset>

                <div class="col-12 col-sm-6 mb-3 c_status">
                    <label class="form-label" for="merge_source">合併發票</label>
                    <select name="merge_source[]" id="merge_source" multiple hidden class="-select2 -multiple form-select @error('merge_source') is-invalid @enderror" data-placeholder="請選擇合併發票">
                        @foreach ($merge_source as $value)
                        <option value="{{ $value->id }}" @if (in_array($value->id, old('merge_source', []))) selected @endif>{{ $value->sn }}</option>
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
                            <input class="form-check-input" name="category" value="B2C" type="radio" id="2c" required {{ ! old('category') || old('category') == 'B2C' ? 'checked' : '' }}>
                            <label class="form-check-label" for="2c">個人</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="category" value="B2B" type="radio" id="2b" required {{ old('category') && old('category') == 'B2B' ? 'checked' : '' }}>
                            <label class="form-check-label" for="2b">公司．企業</label>
                        </div>
                    </div>
                    <div class="invalid-feedback">
                        @error('category')
                        {{ $message }}
                        @enderror
                    </div>
                </fieldset>

                <div class="col-12 col-sm-6 mb-3 c_category d-none">
                    <label class="form-label l_buyer_ubn">公司統編</label>
                    <input type="text" name="buyer_ubn" class="form-control @error('buyer_ubn') is-invalid @enderror" placeholder="請輸入公司統編" aria-label="公司統編" value="{{ old('buyer_ubn') }}" disabled>
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
                    <input type="text" name="buyer_name" class="form-control @error('buyer_name') is-invalid @enderror" placeholder="請輸入買受人名稱" aria-label="買受人名稱" value="{{ old('buyer_name', $order->ord_name) }}" required>
                    <div class="invalid-feedback">
                        @error('buyer_name')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label l_buyer_email">買受人E-mail <span class="text-danger">*</span></label>
                    <input type="email" name="buyer_email" class="form-control @error('buyer_email') is-invalid @enderror" placeholder="請輸入買受人E-mail" aria-label="買受人E-mail" value="{{ old('buyer_email', $order->email) }}" required>
                    <div class="invalid-feedback">
                        @error('buyer_email')
                        {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label l_buyer_address">買受人地址 <span class="text-danger">*</span></label>
                    <input type="text" name="buyer_address" class="form-control @error('buyer_address') is-invalid @enderror" placeholder="請輸入買受人地址" aria-label="買受人地址" value="{{ old('buyer_address', $order->ord_address) }}" required>
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
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="invoice_method" value="print" type="radio" id="print" required {{ old('invoice_method') && old('invoice_method') == 'print' ? 'checked' : '' }}>
                            <label class="form-check-label" for="print">無載具(列印電子發票證明聯)</label>
                        </div>
                        {{--
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="invoice_method" value="give" type="radio" id="give" required {{ old('invoice_method') && old('invoice_method') == 'give' ? 'checked' : '' }}>
                            <label class="form-check-label" for="give">捐贈</label>
                        </div>
                        --}}
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="invoice_method" value="e_inv" type="radio" id="e_inv" required {{ ! old('invoice_method') || old('invoice_method') == 'e_inv' ? 'checked' : '' }}>
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

            <div class="row carrier">
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">載具類型 <span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="carrier_type" value="0" type="radio" id="carrier_mobile" {{ old('carrier_type') && old('carrier_type') == 0 ? 'checked' : '' }}>
                            <label class="form-check-label" for="carrier_mobile">手機條碼載具</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="carrier_type" value="1" type="radio" id="carrier_certificate" {{ old('carrier_type') && old('carrier_type') == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="carrier_certificate">自然人憑證條碼載具</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="carrier_type" value="2" type="radio" id="carrier_member" {{ ! old('carrier_type') || old('carrier_type') == 2 ? 'checked' : '' }}>
                            <label class="form-check-label" for="carrier_member">會員電子發票</label>
                        </div>
                    </div>
                    <div class="invalid-feedback">
                        @error('carrier_type')
                        {{ $message }}
                        @enderror
                    </div>
                </fieldset>

                {{-- 電子發票: 條碼載具 --}}
                <div class="col-12 col-sm-6 mb-3 c_carrier_type carrier_0 d-none">
                    <label class="form-label">載具號碼 <span class="text-danger">*</span></label>
                    <input type="text" name="carrier_num" class="form-control @error('carrier_num') is-invalid @enderror" placeholder="請輸入載具號碼" aria-label="載具號碼" value="{{ old('carrier_num') }}" disabled>
                    <div class="invalid-feedback">
                        @error('carrier_num')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                {{-- 電子發票: 會員電子發票 --}}
                <div class="col-12 col-sm-6 mb-3 c_carrier_type carrier_2">
                    <label class="form-label l_carrier_email">E-mail <span class="text-danger">*</span></label>
                    <input type="text" name="carrier_email" class="form-control @error('carrier_email') is-invalid @enderror"
                        placeholder="請輸入E-mail" aria-label="E-mail" value="{{ old('carrier_email', $customer_email?? '') }}">
                    <div class="invalid-feedback">
                        @error('carrier_email')
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
            <h6>電子發票明細</h6>

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
                //開立狀態
                $('input[type=radio][name=status]').on('click change', function() {
                    if (this.value == 9) {
                        $('.c_status').addClass('d-none');
                        $('#merge_source').prop('disabled', true);
                    } else {
                        $('.c_status').removeClass('d-none');
                        $('#merge_source').prop('disabled', false);
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
                                            `<tr class="new_row">
                                                <td style="display:none">${data.received_sn}</td>
                                                <td>${data.name}</td>
                                                <td>${data.amt}</td>
                                                <td>${data.count}</td>
                                                <td></td>
                                                <td>${data.tax}</td>
                                            </tr>`
                                        );
                                    });
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
                    switch (this.value) {
                        case 'print':   // 列印
                            //Email
                            $('input[type=email][name=buyer_email]').prop({
                                required:false
                            });
                            $('.l_buyer_email').html('買受人E-mail');

                            //地址
                            $('input[type=text][name=buyer_address]').prop({
                                required:true
                            });
                            $('.l_buyer_address').html('買受人地址 <span class="text-danger">*</span>');

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
                            $('.c_carrier_type input').prop({
                                disabled:true,
                                required:false
                            }).val('');
                            break;
                        case 'give':    // 捐贈
                            //Email
                            $('input[type=email][name=buyer_email]').prop({
                                required:false
                            });
                            $('.l_buyer_email').html('買受人E-mail');

                            //地址
                            $('input[type=text][name=buyer_address]').prop({
                                required:false
                            });
                            $('.l_buyer_address').html('買受人地址');

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
                            $('.c_carrier_type input').prop({
                                disabled:true,
                                required:false
                            }).val('');
                            break;
                    
                        case 'e_inv':   // 電子發票
                            //地址
                            $('input[type=text][name=buyer_address]').prop({
                                required:false
                            });
                            $('.l_buyer_address').html('買受人地址');

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
                            break;
                        default:
                            break;
                    }
                });

                //載具類型
                $('input[type=radio][name=carrier_type]').on('click change', function() {
                    switch (this.value) {
                        case '2':   // 會員電子發票
                            $('input[type=email][name=buyer_email]').prop({
                                required:true
                            });
                            $('.l_buyer_email').html('買受人E-mail <span class="text-danger">*</span>');

                            $('.c_carrier_type.carrier_0').addClass('d-none');
                            $('.c_carrier_type.carrier_0 input').prop({
                                disabled:true,
                                required:false
                            }).val('');
                            $('.c_carrier_type.carrier_2').removeClass('d-none');
                            $('.c_carrier_type.carrier_2 input').prop({
                                disabled:false,
                                required:true
                            });
                            break;
                    
                        case '0':   // 手機條碼載具
                        case '1':   // 自然人憑證條碼載具
                        default:
                            $('input[type=email][name=buyer_email]').prop({
                                required:false
                            });
                            $('.l_buyer_email').html('買受人E-mail');

                            $('.c_carrier_type.carrier_0').removeClass('d-none');
                            $('.c_carrier_type.carrier_0 input').prop({
                                disabled:false,
                                required:true
                            });
                            $('.c_carrier_type.carrier_2').addClass('d-none');
                            $('.c_carrier_type.carrier_2 input').prop({
                                disabled:true,
                                required:false
                            }).val('');
                            break;
                    }
                });
            });
        </script>
    @endpush
@endonce
