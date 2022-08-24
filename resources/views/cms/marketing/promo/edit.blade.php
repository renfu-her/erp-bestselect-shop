@extends('layouts.main')
@section('sub-content')
    @if ($method === 'edit')
        <h2 class="mb-4">優惠券名稱</h2>
    @else
        <h2 class="mb-4">新增 優惠劵 / 代碼</h2>
    @endif

    <form id="form1" method="post" action="">
        @method('POST')
        @csrf
        @php
            $editBlock = $method === 'edit' ? 'disabled' : '';
        @endphp
        @if ($method === 'edit')
            <input type="hidden" name="category" value="{{ $data->category_code }}">
        @endif

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">優惠劵活動名稱 <span class="text-danger">*</span></label>
                    <input class="form-control" name="title" type="text" placeholder="請輸入活動名稱" {{ $editBlock }}
                        value="{{ old('title', $data->title ?? '') }}" required aria-label="活動名稱">
                </div>
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">優惠券類型 <span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        @foreach ($dis_categorys as $key => $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="category" type="radio" value="{{ $key }}"
                                        {{ $editBlock }} @if (old('category', $data->category_code ?? 'coupon') == $key) checked @endif>
                                    {{ $value }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
                <div class="col-12 mb-3" data-category="code" hidden>
                    <label class="form-label">優惠劵序號 <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <input type="text" name="sn" class="form-control" value="{{ old('sn', $data->sn ?? '') }}"
                            maxlength="20" disabled placeholder="可自行輸入或按隨機產生鈕" autocomplete="off" {{ $editBlock }}>
                        <button id="generate_coupon_sn" class="btn btn-success" type="button" {{ $editBlock }}>
                            <i class="bi bi-shuffle"></i> 隨機產生序號
                        </button>
                        <div class="valid-feedback invalid-feedback -feedback"></div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3" data-category="code" hidden>
                    <label class="form-label">可用起始日 <span class="small text-secondary">（未填則表示現在）</span></label>
                    <div class="input-group has-validation">
                        <input type="date" name="start_date"
                            value="{{ old('start_date') ? date('Y-m-d', strtotime($data->start_date)) : '' }}"
                            class="form-control @error('start_date') is-invalid @enderror" aria-label="可用起始日" editable
                            norequired />
                        <button class="btn btn-outline-secondary icon" type="button" data-clear data-bs-toggle="tooltip"
                            title="清空時間"><i class="bi bi-calendar-x"></i>
                        </button>
                        <div class="invalid-feedback">
                            @error('start_date')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3" data-category="code" hidden>
                    <label class="form-label">可用結束日<span class="small text-secondary">（未填則表示不會結束）</span></label>
                    <div class="input-group has-validation">
                        <input type="date" name="end_date" value="{{ old('end_date') ? date('Y-m-d', strtotime($data->end_date)) : '' }}"
                            class="form-control @error('end_date') is-invalid @enderror" aria-label="可用結束日" editable
                            norequired />
                        <button class="btn btn-outline-secondary icon" type="button" data-clear data-bs-toggle="tooltip"
                            title="清空時間"><i class="bi bi-calendar-x"></i>
                        </button>
                        <div class="invalid-feedback">
                            @error('end_date')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3" data-category="code" hidden>
                    <label class="form-label">優惠券數量 <span class="text-danger">*</span><span
                            class="small text-secondary">（不限則填0）</span></label>
                    <input type="number" name="max_usage" min="0" value="{{ old('max_usage', $data->max_usage ?? '') }}"
                        class="form-control" placeholder="請輸入優惠券數量" required aria-label="優惠券數量" editable>
                </div>
                <div class="col-12 col-sm-6 mb-3" data-category="coupon" hidden>
                    <label class="form-label">優惠券使用天數<span class="small text-secondary">（未填則表示無限制）</span></label>
                    <div class="input-group flex-nowrap">
                        <input type="number" name="life_cycle" step="1" class="form-control" min="0"
                            value="{{ old('life_cycle', $data->life_cycle ?? '') }}" placeholder="請輸入優惠券使用天數" norequired editable>
                        <span class="input-group-text">天</span>
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">使用優惠券最低消費限制 <span class="text-danger">*</span><span
                            class="small text-secondary">（不限則填0）</span></label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                        <input type="number" name="min_consume" min="0" {{ $editBlock }}
                            value="{{ old('min_consume', $data->min_consume ?? 0) }}" class="form-control"
                            placeholder="請輸入使用優惠券最低消費金額" required>
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">與其他行銷活動併用限制 <span class="text-danger">*</span></label>
                    <select class="form-select" aria-label="與其他行銷活動併用限制" {{ $editBlock }}>
                        <option value="1" selected>無限制</option>
                        <option value="2">任選折扣</option>
                        <option value="3">全館折扣</option>
                        <option value="4">VIP 折扣</option>
                        <option value="5">推薦碼折扣</option>
                        <option value="6">加價購</option>
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">適用商品群組<span class="small text-secondary">（不選為全館適用）</span></label>
                    <select name="collection_id[]" multiple class="-select2 -multiple form-select"
                        data-close-on-select="false" data-placeholder="可多選" {{ $editBlock }}>
                        @foreach ($collections as $key => $value)
                            <option value="{{ $value->id }}" @if (in_array($value->id, $discountCollections)) selected @endif>
                                {{ $value->name }}</option>
                        @endforeach
                    </select>
                </div>
                <fieldset class="col-12 mb-1">
                    <legend class="col-form-label p-0 mb-2">優惠方式 <span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        @foreach ($dis_methods as $key => $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="method_code" type="radio" {{ $editBlock }}
                                        value="{{ $key }}" @if (old('method_code', $data->method_code ?? 'cash') == $key) checked @endif
                                        required>
                                    {{ $value }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>

                {{-- 優惠方式：金額 cash --}}
                <div class="row mb-3 border rounded mx-0 px-0 pt-2" data-method="cash" hidden>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">折扣金額 <span class="text-danger">*</span></label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                            <input type="number" name="discount_value"
                                class="form-control @error('discount_value') is-invalid @enderror" min="0"
                                value="{{ old('method_code', $data->method_code ?? '') === 'cash'? old('discount_value', $data->discount_value ?? ''): '' }}"
                                disabled placeholder="請輸入折扣金額">
                            <div class="invalid-feedback">
                                @if (old('method_code', $data->method_code ?? '') === 'cash')
                                    @error('discount_value')
                                        {{ $message }}
                                    @enderror
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 優惠方式：百分比 percent --}}
                <div class="row mb-3 border rounded mx-0 px-0 pt-2" data-method="percent" hidden>
                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">折扣百分比 <span class="text-danger">*</span>
                            <i class="bi bi-info-circle" data-bs-toggle="tooltip"
                                title="例：100 元商品打 8 折為 80 元，請輸入數字 80，等同 80%" data-bs-placement="right"></i>
                        </label>
                        <div class="input-group has-validation">
                            <input type="number" name="discount_value"
                                class="form-control @error('discount_value') is-invalid @enderror" min="1" max="100"
                                value="{{ old('method_code', $data->method_code ?? '') === 'percent'? old('discount_value', $data->discount_value ?? ''): '' }}"
                                disabled placeholder="請輸入百分比 1 ~ 100">
                            <span class="input-group-text"><i class="bi bi-percent"></i></span>
                            <div class="invalid-feedback">
                                @if (old('method_code', $data->method_code ?? '') === 'percent')
                                    @error('discount_value')
                                        {{ $message }}
                                    @enderror
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="submitDiv">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
                <a href="{{ Route('cms.promo.index') }}" class="btn btn-outline-primary px-4" role="button">返回列表</a>
            </div>
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
        <script>
            const AutoSnLength = 12;
            const AbleControl = {
                required: true,
                disabled: false
            };
            const DisabledControl = {
                required: false,
                disabled: true
            };
            const editBlock = @json($editBlock);

            // init
            setCategory();
            setMethod();

            // 優惠券類型
            $('input[name="category"]').on('change', function() {
                setCategory();
            });
            // 設定優惠券類型
            function setCategory() {
                const category = $('input[name="category"]:checked').val();

                // hidden
                $(`div[data-category]:not([data-category="${category}"])`).prop('hidden', true);
                $(`div[data-category]:not([data-category="${category}"]) input`).prop(DisabledControl);

                // shown
                $(`div[data-category="${category}"]`).prop('hidden', false);
                $(`div[data-category="${category}"] input`).prop(AbleControl);
                $(`div[data-category="${category}"] input[norequired]`).prop('required', false);
                if (editBlock) {
                    $(`div[data-category="${category}"] input:not([editable])`).prop(DisabledControl);
                }

            }

            // 優惠方式
            $('input[name="method_code"]').on('change', function() {
                setMethod();
            });
            // 設定優惠方式
            function setMethod() {
                const method = $('input[name="method_code"]:checked').val();

                // hidden
                $(`div[data-method]:not([data-method="${method}"])`).prop('hidden', true);
                $(`div[data-method]:not([data-method="${method}"])`).find('input, select').prop(DisabledControl);

                // shown
                $(`div[data-method="${method}"]`).prop('hidden', false);
                $(`div[data-method="${method}"]`).find('input, select').prop(AbleControl);
                $(`div[data-method="${method}"]`).find('[norequired]').prop('required', false);
                if (editBlock) {
                    $(`div[data-method="${method}"]`).find('input:not([editable]), select:not([editable])')
                        .prop(DisabledControl);
                }
            }

            $('#form1').submit(function(e) {
                if ($('input[name="category"]:checked').val() === 'code') {
                    const $sn = $('input[name="sn"]');
                    if ($sn.hasClass('is-valid') || $sn.prop('disabled')) {
                        return true;
                    }
                    if ($sn.hasClass('is-invalid')) {
                        toast.show('請填入不重複的優惠劵序號', {
                            type: 'danger'
                        });
                        return false;
                    } else {
                        checkSnInput($sn, true);
                        return false;
                    }
                }
            })

            // 產生優惠劵序號 btn
            $('#generate_coupon_sn').on('click', function() {
                generateCouponSn(AutoSnLength);
            });
            // 檢查序號
            $('input[name="sn"]').on('change', function() {
                checkSnInput($(this), false);
            });

            function checkSnInput($snInput, submit) {
                const sn = $snInput.val();
                if (!sn) {
                    unavailableSn($snInput, '序號不可為空');
                    return false;
                }

                callCheckCouponSnApi(sn).then((res) => {
                    if (res.status === '0') {
                        // 序號可使用
                        availableSn($snInput);
                        if (submit) {
                            $('#form1').submit();
                        }
                    } else {
                        // 序號不可使用
                        let msg = '';
                        switch (res.status) {
                            case 'E01':
                                msg = res.message.sn[0];
                            default:
                                msg = res.message || '此序號不可使用';
                                break;
                        }
                        unavailableSn($snInput, msg);
                    }
                }).catch((err) => {
                    console.error(err);
                    alert('發生錯誤！');
                });
            }

            // 產生優惠劵序號
            function generateCouponSn(len) {
                let result = '';
                const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                for (let index = 0; index < len; index++) {
                    result += characters.charAt(Math.floor(Math.random() * characters.length));
                }

                // 檢查重複
                callCheckCouponSnApi(result).then((res) => {
                    if (res.status === '0') {
                        $('input[name="sn"]').val(result);
                        availableSn($('input[name="sn"]'));
                    } else {
                        generateCouponSn(AutoSnLength);
                    }
                }).catch((err) => {
                    console.error(err);
                    alert('發生錯誤！');
                });
            }

            // 檢查序號是否重複API
            function callCheckCouponSnApi(sn) {
                const _URL = @json(route('api.cms.discount.check-sn'));
                let Data = {
                    sn: sn
                };
                console.log('callCheckCouponSnApi', sn);

                return axios.post(_URL, Data).then((result) => {
                    if (result.status === 200) {
                        return result.data;
                    } else {
                        return Promise.reject(result);
                    }
                }).catch((err) => {
                    console.error(err);
                    alert('發生錯誤！');
                });
            }

            // 序號可使用
            function availableSn($snInput) {
                $snInput.removeClass('is-invalid').addClass('is-valid');
                $snInput.siblings('.-feedback').removeClass('invalid-feedback').addClass('valid-feedback');
                $snInput.siblings('.-feedback').text('序號可使用');
            }
            // 序號不可使用
            function unavailableSn($snInput, errMsg) {
                $snInput.removeClass('is-valid').addClass('is-invalid');
                $snInput.siblings('.-feedback').removeClass('valid-feedback').addClass('invalid-feedback');
                $snInput.siblings('.-feedback').text(errMsg);
            }
        </script>
    @endpush
@endonce
