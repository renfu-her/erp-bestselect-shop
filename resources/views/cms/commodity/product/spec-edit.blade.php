@extends('layouts.main')
@section('sub-content')
    <div>
        <h2 class="mb-3">{{ $data->title }}</h2>
        <x-b-prd-navi id="{{ $data->id }}"></x-b-prd-navi>
    </div>

    <form id="form1" method="POST" action="{{ Route('cms.product.edit-spec', ['id' => $data->id]) }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>編輯規格</h6>
            <div>
                <label>規格最多只能選擇三種</label>
                <p class="mark m-0"><i class="bi bi-exclamation-diamond-fill mx-2 text-warning"></i>已產生SKU將無法再新增修改規格（但仍可新增項目）</p>
                <div class="-appendClone -spec">
                    @if (count($currentSpec) == 0)
                        <div class="pt-4 pb-3 -cloneElem -spec">
                            <div class="row mb-3">
                                <div class="col-auto p-0">
                                    <button type="button"
                                        class="-del -spec icon icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <div class="col col-md-6">
                                    <select class="-single form-select" data-placeholder="請選擇規格" required>
                                        @foreach ($specs as $key => $spec)
                                            <option value="{{ $spec['id'] }}">{{ $spec['title'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-outline-primary px-4 -newItem">新增項目</button>
                                </div>
                            </div>
                            <div class="row -appendClone -item">
                                <div class="col-12 col-sm-6 mb-2 -cloneElem -item">
                                    <div class="input-group has-validation">
                                        <input class="form-control" value="" type="text" required placeholder="請輸入項目"
                                            aria-label="項目">
                                        <button class="btn btn-outline-secondary -del -item" type="button" title="刪除">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @foreach ($currentSpec as $specKey => $cSpec)
                        <div class="pt-4 pb-3 -cloneElem -spec">
                            <div class="row mb-3">
                                <div class="col-auto p-0">
                                    <button type="button" disabled
                                        class="-del -spec icon icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <div class="col col-md-6">
                                    <select class="-single form-select" data-placeholder="請選擇規格" disabled>
                                        @foreach ($specs as $key => $spec)
                                            <option value="{{ $spec['id'] }}" @if ($spec['id'] == $cSpec->id) selected @endif>
                                                {{ $spec['title'] }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" value="{{ $cSpec->id }}">
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-outline-primary px-4 -newItem">新增項目</button>
                                </div>
                            </div>
                            <div class="row -appendClone -item">
                                @foreach ($cSpec->items as $item)
                                    <div class="col-12 col-sm-6 mb-2 -cloneElem -item">
                                        <div class="input-group has-validation">
                                            <input class="form-control" value="{{ $item->value }}" disabled
                                                data-id="{{ $item->key }}" type="text" placeholder="請輸入項目"
                                                aria-label="項目">
                                            <button class="btn btn-outline-secondary -del -item" disabled type="button"
                                                title="刪除"><i class="bi bi-x-lg"></i></button>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                </div>
                @if ($data->spec_locked)
                <div class="d-grid gap-2 border-top -newSpecBtnBox">
                    <button type="button" class="btn btn-outline-primary border-dashed mt-4 -newSpec"
                        style="font-weight: 500;">
                        <i class="bi bi-plus-circle"></i> 新增規格
                    </button>
                </div>
                @endif
            </div>
        </div>

        <div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4 -checkSubmit">儲存</button>
                <a href="{{ Route('cms.product.edit-style', ['id' => $data->id]) }}" class="btn btn-outline-primary px-4"
                    role="button">取消</a>
            </div>
        </div>
    </form>

@endsection
@once
    @push('sub-styles')
        <style>
            .-appendClone.-spec .-cloneElem.-spec:not(:last-child) {
                border-bottom: 1px solid #c4c4c4;
            }

        </style>
    @endpush
    @push('sub-scripts')
        <script>
            // clone 規格、項目
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
            // init
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
            $('.-single:disabled').addClass('form-select');
            $('.-single:not(:disabled)').addClass('-select2').select2();
            checkStylesQty();
            // 新增規格
            $('.-newSpec').off('click').on('click', function() {
                Clone_bindCloneBtn($cloneSpec, function($c_s) {
                    $c_s.find('input, select').val('');
                    $c_s.find('input, select, button').prop('disabled', false);
                    $c_s.find(Items.clone + ':nth-child(n+2), select.-single + input:hidden').remove();
                    $c_s.find('select.-single').addClass('-select2').select2();

                    // 規格裡的btn: 新增項目
                    $c_s.find('.-newItem').off('click').on('click', function() {
                        Clone_bindCloneBtn($cloneItem, function($c_i) {
                            $c_i.find('input').val('');
                            $c_i.find('input, button').prop('disabled', false);
                        }, {
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
            $('.-newItem').off('click').on('click', function() {
                const $this = $(this);
                Clone_bindCloneBtn($cloneItem, function($c_i) {
                    $c_i.find('input').val('');
                    $c_i.find('input, button').prop('disabled', false);
                }, {
                    cloneElem: Items.clone,
                    delElem: Items.del,
                    $thisAppend: $this.closest(Spec.clone).children(Items.append),
                    checkFn: checkStylesQty
                });
            });
            // 數量檢查
            function checkStylesQty() {
                const spec_qty = $(Spec.clone).length;
                let chkItems = true;

                // 規格最多三種
                $('.-newSpecBtnBox').toggleClass('d-none', spec_qty >= 3);
                // $('.-newSpec').prop('disabled', (spec_qty >= 3));

                // 至少一個規格
                chkItems &= (spec_qty > 0);
                // 每個規格至少一個項目
                $(Spec.clone).each(function(index, element) {
                    chkItems &= ($(element).find(Items.clone).length > 0);
                });
                $('.-checkSubmit').prop('disabled', !chkItems);
            }

            // 儲存前設定name
            $('#form1').submit(function(e) {
                $(Spec.clone).each(function(index, element) {
                    // element == this
                    $(element).find('select.-single.-select2, select.-single + input:hidden').attr('name',
                        'spec' + index);
                    $(element).find(Items.clone + ' input').attr('name', 'item' + index + '[]');
                });
            });
        </script>
    @endpush
@endOnce
