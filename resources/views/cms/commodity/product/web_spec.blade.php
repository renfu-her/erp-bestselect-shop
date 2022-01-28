@extends('layouts.main')
@section('sub-content')
<div>
    <h2 class="mb-3">{{ $product->title }}</h2>
    <x-b-prd-navi :product="$product"></x-b-prd-navi>
</div>
<form action="">
    <div id="specList" class="card shadow p-4 mb-4">
        <h6>規格說明（網頁）</h6>
        <div class="sortabled mb-3 -appendClone">
            <div class="mb-2 row sortabled_box -cloneElem">
                <div class="col d-flex flex-column flex-sm-row pe-0">
                    <div class="col col-sm-5 col-lg-3 px-0 pb-2 pb-sm-0">
                        <input type="text" class="form-control" maxlength="10" placeholder="請輸入標題。例：材質" aria-label="規格說明標題">
                    </div>
                    <div class="col px-0 px-sm-2 pb-2 pb-sm-0">
                        <input type="text" class="form-control" placeholder="請輸入內文。例：棉 / 聚脂纖維" aria-label="規格說明內文">
                    </div>
                </div>
                <div class="col-auto d-flex flex-column flex-sm-row ps-0">
                    <span type="button" class="icon -move icon-btn fs-5 text-primary rounded-circle border-0 p-0">
                        <i class="bi bi-arrows-move"></i>
                    </span>
                    <button type="button" class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="w-100 my-2 p-0 dropdown-divider d-block d-sm-none"></div>
            </div>
        </div>
        <!-- 新增鈕 -->
        <div class="d-grid gap-2">
            <button type="button" class="btn btn-outline-primary border-dashed -newSpec" style="font-weight: 500;">
                <i class="bi bi-plus-circle"></i> 新增
            </button>
        </div>
        <!-- 新增鈕 end -->
    </div>
    <div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ Route('cms.product.index') }}" class="btn btn-outline-primary px-4" role="button">返回列表</a>
        </div>
    </div>
</form>
@endsection
@once
    @push('sub-styles')
    <style>
        .sortabled .placeholder-highlight {
            height: 109px;
        }
        @media (min-width: 576px) {
            .sortabled .placeholder-highlight {
                height: 38px;
            }
        }
    </style>
    @endpush
    @push('sub-scripts')
        <script>
            // clone 項目
            const $clone = $('.-cloneElem:first-child').clone();
            $('.-cloneElem.d-none').remove();

            // 顯示字數
            showWordsLength($('input[maxlength]'));
            // 拖曳
            bindMove();
            // 刪除
            Clone_bindDelElem($('.-cloneElem .-del'));
            
            $('.-newSpec').off('click').on('click', function() {
                Clone_bindCloneBtn($clone, function (cloneElem) {
                    cloneElem.find('input').val('');
                    showWordsLength(cloneElem.find('input[maxlength]'));
                });
                // 拖曳
                bindMove();
            });

            // bind 拖曳
            function bindMove() {
                bindSortableMove($('#specList .sortabled'), {
                    axis: 'y',
                    placeholder: 'placeholder-highlight mb-2',
                    activate: function (e, ui) {
                        ui.item.find('.dropdown-divider').removeClass('d-block').hide();
                    },
                    stop: function (e, ui) {
                        ui.item.find('.dropdown-divider').addClass('d-block').show();
                    }
                });
            }
        </script>
    @endpush
@endOnce
