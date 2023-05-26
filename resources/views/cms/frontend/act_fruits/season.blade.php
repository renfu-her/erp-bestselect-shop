@extends('layouts.main')
@section('sub-content')
<h2 class="mb-4">水果分類設定</h2>

<ul class="nav nav-tabs border-bottom-0">
    <li class="nav-item">
        <button class="nav-link active" data-page="tab1" aria-current="page" type="button">
            春季水果<br><span class="small">(3至5月)</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-page="tab2" type="button">
            夏季水果<br><span class="small">(6至8月)</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-page="tab3" type="button">
            秋季水果<br><span class="small">(9至11月)</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-page="tab4" type="button">
            冬季水果<br><span class="small">(12至2月)</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-page="tab5" type="button">
            進口水果<br><span class="small">(1至12月)</span>
        </button>
    </li>
</ul>

<form action="" method="post">
    <div id="tab1" class="-page">
        <input type="hidden" name="tab_id[]" value="1">
        <div class="card shadow p-4 mb-4">
            <h6 class="mb-2">春季水果 (3至5月)</h6>
            <p class="mb-2">拖曳方框以排序
                <button type="button" class="btn btn-outline-primary border-dashed btn-sm mx-2"
                    data-bs-toggle="modal" data-bs-target="#addFruit">
                    <i class="bi bi-plus-circle bold"></i> 加入水果
                </button>
            </p>
            <ul class="sortable mb-3 -serial-number">
                <li>
                    <label class="-serial-title -before">茂谷柑</label>
                    <input type="hidden" name="fruit_1[]" value="1">
                    <span class="icon icon-btn fs-5 text-danger rounded-circle border-0 -del">
                        <i class="bi bi-trash"></i>
                    </span>
                </li>
                <li>
                    <label class="-serial-title -before">玉女小番茄</label>
                    <input type="hidden" name="fruit_1[]" value="2">
                    <span class="icon icon-btn fs-5 text-danger rounded-circle border-0 -del">
                        <i class="bi bi-trash"></i>
                    </span>
                </li>
                <li>
                    <label class="-serial-title -before">橙蜜香小番茄</label>
                    <input type="hidden" name="fruit_1[]" value="3">
                    <span class="icon icon-btn fs-5 text-danger rounded-circle border-0 -del">
                        <i class="bi bi-trash"></i>
                    </span>
                </li>
                <li>
                    <label class="-serial-title -before">鳳梨釋迦</label>
                    <input type="hidden" name="fruit_1[]" value="4">
                    <span class="icon icon-btn fs-5 text-danger rounded-circle border-0 -del">
                        <i class="bi bi-trash"></i>
                    </span>
                </li>
            </ul>
        </div>
    </div>
    <div id="tab2" class="-page" hidden>
        <input type="hidden" name="tab_id[]" value="2">
        <div class="card shadow p-4 mb-4">
            <h6 class="mb-2">夏季水果 (6至8月)</h6>
            <p class="mb-2">拖曳方框以排序
                <button type="button" class="btn btn-outline-primary border-dashed btn-sm mx-2"
                    data-bs-toggle="modal" data-bs-target="#addFruit">
                    <i class="bi bi-plus-circle bold"></i> 加入水果
                </button>
            </p>
            <ul class="sortable mb-3 -serial-number">
            </ul>
        </div>
    </div>
    <div id="tab3" class="-page" hidden>
        <input type="hidden" name="tab_id[]" value="3">
        <div class="card shadow p-4 mb-4">
            <h6 class="mb-2">秋季水果 (9至11月)</h6>
            <p class="mb-2">拖曳方框以排序
                <button type="button" class="btn btn-outline-primary border-dashed btn-sm mx-2"
                    data-bs-toggle="modal" data-bs-target="#addFruit">
                    <i class="bi bi-plus-circle bold"></i> 加入水果
                </button>
            </p>
            <ul class="sortable mb-3 -serial-number">
            </ul>
        </div>
    </div>
    <div id="tab4" class="-page" hidden>
        <input type="hidden" name="tab_id[]" value="4">
        <div class="card shadow p-4 mb-4">
            <h6 class="mb-2">冬季水果 (12至2月)</h6>
            <p class="mb-2">拖曳方框以排序
                <button type="button" class="btn btn-outline-primary border-dashed btn-sm mx-2"
                    data-bs-toggle="modal" data-bs-target="#addFruit">
                    <i class="bi bi-plus-circle bold"></i> 加入水果
                </button>
            </p>
            <ul class="sortable mb-3 -serial-number">
            </ul>
        </div>
    </div>
    <div id="tab5" class="-page" hidden>
        <input type="hidden" name="tab_id[]" value="5">
        <div class="card shadow p-4 mb-4">
            <h6 class="mb-2">進口水果 (1至12月)</h6>
            <p class="mb-2">拖曳方框以排序
                <button type="button" class="btn btn-outline-primary border-dashed btn-sm mx-2"
                    data-bs-toggle="modal" data-bs-target="#addFruit">
                    <i class="bi bi-plus-circle bold"></i> 加入水果
                </button>
            </p>
            <ul class="sortable mb-3 -serial-number">
                <li>
                    <label class="-serial-title -before">日本水蜜桃</label>
                    <input type="hidden" name="fruit_5[]" value="27">
                    <span class="icon icon-btn fs-5 text-danger rounded-circle border-0 -del">
                        <i class="bi bi-trash"></i>
                    </span>
                </li>
                <li>
                    <label class="-serial-title -before">日本麝香葡萄</label>
                    <input type="hidden" name="fruit_5[]" value="28">
                    <span class="icon icon-btn fs-5 text-danger rounded-circle border-0 -del">
                        <i class="bi bi-trash"></i>
                    </span>
                </li>
                <li>
                    <label class="-serial-title -before">日本水蜜桃蘋果</label>
                    <input type="hidden" name="fruit_5[]" value="29">
                    <span class="icon icon-btn fs-5 text-danger rounded-circle border-0 -del">
                        <i class="bi bi-trash"></i>
                    </span>
                </li>
                <li>
                    <label class="-serial-title -before">日本陽光蜜富士</label>
                    <input type="hidden" name="fruit_5[]" value="30">
                    <span class="icon icon-btn fs-5 text-danger rounded-circle border-0 -del">
                        <i class="bi bi-trash"></i>
                    </span>
                </li>
                <li>
                    <label class="-serial-title -before">秘魯無籽葡萄</label>
                    <input type="hidden" name="fruit_5[]" value="31">
                    <span class="icon icon-btn fs-5 text-danger rounded-circle border-0 -del">
                        <i class="bi bi-trash"></i>
                    </span>
                </li>
            </ul>
        </div>
    </div>

    <div class="col-auto">
        <button type="submit" class="btn btn-primary px-4">儲存</button>
        <a href="{{ Route('cms.act-fruits.index') }}" class="btn btn-outline-primary px-4"
            role="button">返回列表</a>
    </div>
</form>

<x-b-modal id="addFruit" cancelBtn="false" size="modal-lg">
    <x-slot name="title">選取水果</x-slot>
    <x-slot name="body">
        <div class="row">
            <div class="col-12">
                <select multiple class="-multiple form-select">
                    <option value="1">茂谷柑</option>
                    <option value="2">玉女小番茄</option>
                    <option value="3">橙蜜香小番茄</option>
                    <option value="4">鳳梨釋迦</option>
                    <option value="5">大目釋迦</option>
                    <option value="6">牛奶蜜棗</option>
                    <option value="7">貴妃枇杷</option>
                    <option value="8">玉荷包</option>
                    <option value="27">日本水蜜桃</option>
                    <option value="28">日本麝香葡萄</option>
                    <option value="29">日本水蜜桃蘋果</option>
                    <option value="30">日本陽光蜜富士</option>
                    <option value="31">秘魯無籽葡萄</option>
                </select>
            </div>
        </div>
    </x-slot>
    <x-slot name="foot">
        <button type="button" class="btn btn-primary btn-ok">加入分類</button>
    </x-slot>
</x-b-modal>
@endsection

@once
    @push('sub-styles')
        <style>
            /* 拖曳塊 */
            .sortable {
                list-style-type: none;
                margin: 0;
                padding: 0;
                position: relative;
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
            }
            .sortable li {
                margin: 10px;
                padding: 5px 10px;
                background-color: var(--bs-light);
                box-shadow: 0 0 0 2px var(--bs-green);
                border-radius: 10px;
                display: flex;
                align-items: center;
                width: calc(50% - 20px);
                cursor: move;
            }
            .sortable li label {
                flex: 1;
                cursor: move;
            }
            @media screen and (max-width: 768px) {
                .sortable li {
                    width: 100%;
                }
            }
            /* 拖曳預覽框 */
            .sortable li.placeholder-highlight {
                height: 50px;
                box-shadow: none;
                background-color: #ffe00030;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            delFn();
            $('#addFruit select.-multiple').select2({
                closeOnSelect: false
            });
            // Tabs
            $('.nav-link').off('click').on('click', function() {
                const $this = $(this);
                const page = $this.data('page');

                // tab
                $('.nav-link').removeClass('active').removeAttr('aria-current');
                $this.addClass('active').attr('aria-current', 'page');
                // page
                $('.-page').prop('hidden', true);
                $(`#${page}`).prop('hidden', false);
            });

            // sortable
            $('.sortable').sortable({
                placeholder: 'placeholder-highlight',
                cursor: 'move'
            });

            // 加入水果Modal
            const addFruitModal = new bootstrap.Modal(document.getElementById('addFruit'), {
                backdrop: 'static'
            });
            // -- 開啟 Modal
            $('#addFruit').on('show.bs.modal', function (e) {
                // console.log(e.relatedTarget);
                const $page = $(e.relatedTarget).closest('.-page');
                let id = [];
                $('input[name^="fruit_"]', $page).each(function (index, element) {
                    // element == this
                    id.push($(element).val());
                });
                $('#addFruit select.-multiple').val(id);
                $('#addFruit select.-multiple').trigger('change');
                $('#addFruit .btn-ok').data('tab', $page.attr('id'));
            });
            // -- 加入
            $('#addFruit .btn-ok').off('click.add').on('click.add', function () {
                const tab = $(this).data('tab');
                const $page = $(`#${tab}`);
                const $ul = $('ul.sortable', $page);
                const id = $('input[name="tab_id[]"]', $page).val();
                const _newFruits = $('#addFruit select.-multiple').select2('data');
                $('.sortable li', $page).addClass('del');

                _newFruits.forEach(data => {
                    if ($(`input[name^="fruit_"][value="${data.id}"]`, $page).length > 0) {
                        $(`.sortable li:has(input[name^="fruit_"][value="${data.id}"])`, $page).removeClass('del');
                    } else {
                        $ul.append(`
                            <li>
                                <label class="-serial-title -before">${data.text}</label>
                                <input type="hidden" name="fruit_${id}[]" value="${data.id}">
                                <span class="icon icon-btn fs-5 text-danger rounded-circle border-0 -del">
                                    <i class="bi bi-trash"></i>
                                </span>
                            </li>
                        `);
                    }
                });
                
                // 刪除
                $('.sortable li.del', $page).remove();
                delFn($page);
                // 關閉懸浮視窗
                addFruitModal.hide();
            });
            // -- 刪除 .-del
            function delFn(page = '') {
                $('.-del', page).off('click').on('click', function () {
                    $(this).closest('li').remove();
                });
            }
        </script>
    @endpush
@endOnce
