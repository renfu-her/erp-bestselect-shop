@extends('layouts.main')
@section('sub-content')
    @if ($method === 'edit')
        <h2 class="mb-3">優惠券名稱</h2>
    @else
        <h2 class="mb-3">新增 優惠劵 / 序號</h2>
    @endif

    <form id="form1" method="post" action="">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">優惠劵活動名稱 <span class="text-danger">*</span></label>
                    <input class="form-control" name="title" type="text" placeholder="請輸入活動名稱" required aria-label="活動名稱">
                </div>
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">優惠券類型 <span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="category" type="radio" value="coupon">
                                一般券
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="category" type="radio" value="code">
                                序號兌換
                            </label>
                        </div>
                    </div>
                </fieldset>
                <div class="col-12 mb-3" data-category="code" hidden>
                    <label class="form-label">優惠劵序號 <span class="text-danger">*</span><span class="small text-secondary">（僅接受英數，區分大小寫）</span></label>
                    <div class="input-group has-validation">
                        <input type="text" name="sn" class="form-control" value="" maxlength="20" disabled placeholder="可自行輸入或按隨機產生鈕" autocomplete="off">
                        <button id="generate_coupon_sn" class="btn btn-success" type="button">
                            <i class="bi bi-shuffle"></i> 隨機產生序號
                        </button>
                        <div class="valid-feedback invalid-feedback -feedback"></div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">活動開始日期<span class="small text-secondary">（未填則表示現在）</span></label>
                    <div class="input-group has-validation">
                        <input type="date" name="start_date" value=""
                                class="form-control" aria-label="活動開始日期"/>
                        <button class="btn btn-outline-secondary icon" type="button" data-clear
                                data-bs-toggle="tooltip" title="清空日期"><i class="bi bi-calendar-x"></i>
                        </button>
                        <div class="invalid-feedback">
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">活動結束日期<span class="small text-secondary">（未填則表示不會結束）</span></label>
                    <div class="input-group has-validation">
                        <input type="date" name="end_date" value=""
                                class="form-control" aria-label="活動結束日期"/>
                        <button class="btn btn-outline-secondary icon" type="button" data-clear
                                data-bs-toggle="tooltip" title="清空日期"><i class="bi bi-calendar-x"></i>
                        </button>
                        <div class="invalid-feedback">
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">優惠券數量 <span class="text-danger">*</span><span class="small text-secondary">（不限則填0）</span></label>
                    <input type="number" name="" min="0" value="1000" class="form-control" placeholder="請輸入優惠券數量" required aria-label="優惠券數量">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">使用優惠券最低消費限制 <span class="text-danger">*</span><span class="small text-secondary">（不限則填0）</span></label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                        <input type="number" name="" min="0" value="0" class="form-control" placeholder="請輸入使用優惠券最低消費金額" required>
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">優惠券使用天數<span class="small text-secondary">（未填則表示無限制）</span></label>
                    <div class="input-group flex-nowrap">
                        <input type="number" name="" step="1" class="form-control" min="0" value="" placeholder="請輸入優惠券使用天數" norequired>
                        <span class="input-group-text">天</span>
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">與其他行銷活動併用限制 <span class="text-danger">*</span></label>
                    <select class="form-select" aria-label="與其他行銷活動併用限制">
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
                    <select name="select[]" multiple class="-select2 -multiple form-select" data-close-on-select="false" data-placeholder="可多選">
                        <option value="1">item 1</option>
                        <option value="2">item 2</option>
                        <option value="3">item 3</option>
                    </select>
                </div>
                <fieldset class="col-12 mb-1">
                    <legend class="col-form-label p-0 mb-2">優惠方式 <span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="method_code" type="radio" value="cash" required>
                                金額
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="method_code" type="radio" value="percent" required>
                                百分比
                            </label>
                        </div>
                    </div>
                </fieldset>

                {{-- 優惠方式：金額 cash --}}
                <div class="row mb-3 border rounded mx-0 px-0 pt-2" data-method="cash" hidden>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">折扣金額 <span class="text-danger">*</span></label>
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                            <input type="number" name="" class="form-control" min="0" value="" disabled placeholder="請輸入折扣金額">
                        </div>
                    </div>
                    <fieldset class="col-12 col-sm-6 mb-3">
                        <legend class="col-form-label p-0 mb-2">&nbsp;</legend>
                        <div class="px-1 pt-1">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="" type="checkbox" value="1" disabled checked norequired>
                                    累計折扣
                                </label>
                            </div>
                        </div>
                    </fieldset>
                </div>

                {{-- 優惠方式：百分比 percent --}}
                <div class="row mb-3 border rounded mx-0 px-0 pt-2" data-method="percent" hidden>
                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">折扣百分比 <span class="text-danger">*</span>
                            <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="例：100 元商品打 8 折為 80 元，請輸入數字 80，等同 80%" data-bs-placement="right"></i>
                        </label>
                        <div class="input-group flex-nowrap">
                            <input type="number" name="" class="form-control" min="1" max="100" value="" disabled placeholder="請輸入百分比 1 ~ 100">
                            <span class="input-group-text"><i class="bi bi-percent"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="submitDiv">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
                <a href="{{ Route('cms.promo.index') }}" class="btn btn-outline-primary px-4"
                   role="button">返回列表</a>
            </div>
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
        <script>
            const AutoSnLength = 12;
            const AbleControl = { required: true, disabled: false };
            const DisabledControl = { required: false, disabled: true };

            // init
            setCategory();
            setMethod();

            // 優惠券類型
            $('input[name="category"]').on('change', function () {
                setCategory();
            });
            // 設定優惠券類型
            function setCategory() {
                const category = $('input[name="category"]:checked').val();

                if (category === 'code') {
                    $('div[data-category="code"]').prop('hidden', false);
                    $('div[data-category="code"] input').prop(AbleControl);
                } else {
                    $('div[data-category="code"]').prop('hidden', true);
                    $('div[data-category="code"] input').prop(DisabledControl);
                }
            }

            // 優惠方式
            $('input[name="method_code"]').on('change', function () {
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
            }

            $('#form1').submit(function (e) {
                if ($('input[name="category"]:checked').val() === 'code') {
                    e.preventDefault();

                    const $sn = $('input[name="sn"]');
                    if ($sn.hasClass('is-valid')) {
                        $(this).submit();
                    } if ($sn.hasClass('is-invalid')) {
                        toast.show('請填入不重複的優惠劵序號', { type: 'danger' });
                        return false;
                    } else {
                        checkSnInput($sn);
                    }
                }
            })

            // 產生優惠劵序號 btn
            $('#generate_coupon_sn').on('click', function () {
                generateCouponSn(AutoSnLength);
            });
            // 檢查序號
            $('input[name="sn"]').on('change', function () {
                checkSnInput($(this));
            });
            function checkSnInput($snInput) {
                const sn = $snInput.val();
                if (!sn) {
                    unavailableSn($snInput, '序號不可為空');
                    return false;
                }

                callCheckCouponSnApi(sn).then((res) => {
                    if (res.status === '0') {
                        // 序號可使用
                        availableSn($snInput);
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
                const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
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

