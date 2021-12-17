@extends('layouts.main')
@section('sub-content')
<div>
    <h2 class="mb-3">{{ $data->title }}</h2>
    <x-b-prd-navi id="{{  $data->id }}"></x-b-prd-navi>
</div>

<div class="card shadow p-4 mb-4">
    <h6>編輯規格</h6>
    <label>規格最多只能選擇三種</label>
    <div class="mb-4 -appendClone -spec">
        <div class="border-bottom pt-4 pb-3 -cloneElem -spec">
            <div class="row mb-3">
                <div class="col-auto p-0">
                    <button type="button" class="-del -spec icon icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="col-8 col-md-6 col-xl-4">
                    <select name="" class="-select2 -single" data-placeholder="請選擇規格">
                        <option value="1">item 1</option>
                        <option value="2">item 2</option>
                        <option value="3">item 3</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-primary px-4 -newItem">新增項目</button>
                </div>
            </div>
            <div class="row -appendClone -item">
                <div class="col-12 col-sm-6 mb-2 -cloneElem -item">
                    <div class="input-group has-validation">
                        <input class="form-control" value="" name=""
                        type="text" placeholder="請輸入項目" aria-label="項目">
                        <button class="btn btn-outline-secondary -del -item" type="button"><i class="bi bi-x-lg"></i></button>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-grid gap-2">
        <button type="button" class="btn btn-outline-primary border-dashed -newSpec" style="font-weight: 500;">
            <i class="bi bi-plus-circle"></i> 新增規格
        </button>
    </div>
</div>

<div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary px-4 -checkSubmit">儲存</button>
        <a href="{{ Route('cms.product.edit-style', ['id' => $data->id]) }}" class="btn btn-outline-primary px-4" role="button">返回列表</a>
    </div>
</div>


@endsection
@once
    @push('sub-styles')
    <style>
    </style>
    @endpush
    @push('sub-scripts')
        <script>
            // clone 項目
            const Spec = {
                append: '.-appendClone.-spec',
                clone: '.-cloneElem.-spec',
                del: '.-del.-spec'
            };
            const Items = {
                append: '.-appendClone.-item',
                clone: '.-cloneElem.-item',
                del: '.-del.-item'
            };
            const $cloneSpec = $(Spec.clone + ':first-child').clone();
            const $cloneItem = $(Spec.clone + ':first-child ' + Items.clone + ':first-child').clone();
            // init .-del
            Clone_bindDelElem($(Spec.del), {
                appendClone: Spec.append,
                cloneElem: Spec.clone,
                checkFn: checkStylesQty
            });
            Clone_bindDelElem($(Items.del), {
                appendClone: Items.append,
                cloneElem: Items.clone,
                checkFn: checkStylesQty
            });
            // 新增規格
            $('.-newSpec').off('click').on('click', function () {
                Clone_bindCloneBtn($cloneSpec, function ($c_s) {
                    $c_s.find('input, select').val('');
                    $c_s.find(Spec.del).prop('disabled', false);
                    $c_s.find(Items.clone + ':nth-child(n+2)').remove();

                    // 規格裡的btn: 新增項目
                    $c_s.find('.-newItem').off('click').on('click', function () {
                        Clone_bindCloneBtn($cloneItem, '', {
                            cloneElem: Items.clone,
                            delElem: Items.del,
                            $thisAppend: $(this).closest(Spec.clone).children(Items.append),
                            checkFn: checkStylesQty
                        });
                    });
                    // 規格裡的btn: 刪除項目
                    Clone_bindDelElem($c_s.find(Items.del), {
                        appendClone: Items.append,
                        cloneElem: Items.clone,
                        checkFn: checkStylesQty
                    });
                }, {
                    appendClone: Spec.append,
                    cloneElem: Spec.clone,
                    delElem: Spec.del,
                    checkFn: checkStylesQty
                });
            });
            // 新增項目
            $('.-newItem').off('click').on('click', function () {
                Clone_bindCloneBtn($cloneItem, '', {
                    cloneElem: Items.clone,
                    delElem: Items.del,
                    $thisAppend: $(this).closest(Spec.clone).children(Items.append),
                    checkFn: checkStylesQty
                });
            });
            // 數量檢查
            function checkStylesQty() {
                const spec_qty = $(Spec.clone).length;
                let chkItems = true;

                // 規格最多三種
                $('.-newSpec').prop('disabled', (spec_qty >= 3));

                // 至少一個規格
                chkItems &= (spec_qty > 0);
                // 每個規格至少一個項目
                $(Spec.clone).each(function (index, element) {
                    chkItems &= ($(element).find(Items.clone).length > 0);
                });
                $('.-checkSubmit').prop('disabled', !chkItems);
            }
        </script>
    @endpush
@endOnce
