@extends('layouts.main')
@section('sub-content')
    @if ($method === 'edit')
        <h2 class="mb-3">活動名稱</h2>
    @else
        <h2 class="mb-3">新增 現折優惠</h2>
    @endif

    <form id="form1" method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">活動名稱 <span class="text-danger">*</span></label>
                    <input class="form-control" name="title" type="text" placeholder="請輸入活動名稱" required aria-label="活動名稱">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">消費金額 <span class="text-danger">*</span></label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                        <input type="number" class="form-control" name="min_consume" min="0" value="" placeholder="請輸入消費金額" required>
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">活動開始時間<span class="small text-secondary">（未填則表示現在）</span></label>
                    <div class="input-group has-validation">
                        <input type="datetime-local" name="start_date" value="" class="form-control"
                            aria-label="活動開始時間" />
                        <button class="btn btn-outline-secondary icon" type="button" data-clear data-bs-toggle="tooltip"
                            title="清空時間"><i class="bi bi-calendar-x"></i>
                        </button>
                        <div class="invalid-feedback">
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">活動結束時間<span class="small text-secondary">（未填則表示不會結束）</span></label>
                    <div class="input-group has-validation">
                        <input type="datetime-local" name="end_date" value="" class="form-control" aria-label="活動結束時間" />
                        <button class="btn btn-outline-secondary icon" type="button" data-clear data-bs-toggle="tooltip"
                            title="清空時間"><i class="bi bi-calendar-x"></i>
                        </button>
                        <div class="invalid-feedback">
                        </div>
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">適用商品群組<span class="small text-secondary">（不選為全館適用）</span></label>
                    <select name="collection_id[]" multiple class="-select2 -multiple form-select" data-close-on-select="false"
                        data-placeholder="可多選">
                        @foreach ($collections as $key => $value)
                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                        @endforeach

                    </select>
                </div>
                <fieldset class="col-12 mb-1">
                    <legend class="col-form-label p-0 mb-2">優惠方式 <span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        @foreach ($dis_methods as $key => $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="method_code" type="radio"
                                        value="{{ $key }}" @if (old('method_code', 'cash') == $key) checked @endif
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
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                            <input type="number" name="discount_value" class="form-control" min="0" value="" placeholder="請輸入折扣金額">
                        </div>
                    </div>
                    <fieldset class="col-12 col-sm-6 mb-3">
                        <legend class="col-form-label p-0 mb-2">&nbsp;</legend>
                        <div class="px-1 pt-1">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="is_grand_total" type="checkbox" value="1" checked norequired>
                                    累計折扣
                                </label>
                            </div>
                        </div>
                    </fieldset>
                </div>

                {{-- 優惠方式：百分比 percent --}}
                <div class="row mb-3 border rounded mx-0 px-0 pt-2" data-method="percent" hidden>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">折扣百分比 <span class="text-danger">*</span>
                            <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="例：100 元商品打 8 折為 80 元，請輸入數字 80，等同 80%" data-bs-placement="right"></i>
                        </label>
                        <div class="input-group flex-nowrap">
                            <input type="number" name="" class="form-control" min="1" max="100" value=""
                                placeholder="請輸入百分比 1 ~ 100">
                            <span class="input-group-text"><i class="bi bi-percent"></i></span>
                        </div>
                    </div>
                </div>

                {{-- 優惠方式：優惠劵 coupon --}}
                <div class="row mb-3 border rounded mx-0 px-0 pt-2" data-method="coupon" hidden>
                    <div class="col-12 mb-3">
                        <label class="form-label">指定贈送優惠券 <span class="text-danger">*</span></label>
                        <select class="form-select -select2 -single" aria-label="指定贈送優惠券">
                            <option value="" selected disabled>請選擇</option>
                            <option value="1">item 1</option>
                            <option value="2">item 2</option>
                            <option value="3">item 3</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div id="submitDiv">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
                <a href="{{ Route('cms.discount.index') }}" class="btn btn-outline-primary px-4" role="button">返回列表</a>
            </div>
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
        <script>
            const AbleControl = {
                required: true,
                disabled: false
            };
            const DisabledControl = {
                required: false,
                disabled: true
            };

            // init
            setMethod();
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
            }
        </script>
    @endpush
@endonce
