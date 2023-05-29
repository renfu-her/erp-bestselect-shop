@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">水果分類設定</h2>

    <ul class="nav nav-tabs border-bottom-0">
        @foreach ($collection as $key => $value)
            <li class="nav-item">
                <button class="nav-link active" data-page="tab{{ $key + 1 }}" aria-current="page" type="button">
                    {{ $value->title }}<br><span class="small">{{ $value->sub_title }}</span>
                </button>
            </li>
        @endforeach

    </ul>

    <form action="{{ route('cms.act-fruits.season') }}" method="post">
        @csrf
        @foreach ($collection as $key => $value)
            <div id="tab{{ $key + 1 }}" class="-page" @if ($key != 0) hidden @endif>
                <input type="hidden" name="tab_id[]" value="{{ $value->id }}">
                <div class="card shadow p-4 mb-4">
                    <h6 class="mb-2">{{ $value->title }} {{ $value->sub_title }}</h6>
                    <p class="mb-2">拖曳方框以排序
                        <button type="button" class="btn btn-outline-primary border-dashed btn-sm mx-2"
                            data-bs-toggle="modal" data-bs-target="#addFruit">
                            <i class="bi bi-plus-circle bold"></i> 加入水果
                        </button>
                    </p>
                    <ul class="sortable mb-3 -serial-number">
                        @if (isset($collectionFruits[$value->id]))
                            @foreach ($collectionFruits[$value->id] as $fruit)
                                <li>
                                    <label class="-serial-title -before">{{ $fruit->fruit_title }}</label>
                                    <input type="hidden" name="fruit_{{ $key }}[]"
                                        value="{{ $fruit->fruit_id }}">
                                    <span class="icon icon-btn fs-5 text-danger rounded-circle border-0 -del">
                                        <i class="bi bi-trash"></i>
                                    </span>
                                </li>
                            @endforeach
                        @endif

                    </ul>
                </div>
            </div>
        @endforeach


        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ Route('cms.act-fruits.index') }}" class="btn btn-outline-primary px-4" role="button">返回列表</a>
        </div>
    </form>

    <x-b-modal id="addFruit" cancelBtn="false" size="modal-lg">
        <x-slot name="title">選取水果</x-slot>
        <x-slot name="body">
            <div class="row">
                <div class="col-12">
                    <select multiple class="-multiple form-select">
                        @foreach ($fruits as $key => $value)
                            <option value="{{ $value->id }}">{{ $value->title }}</option>
                        @endforeach
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
            $('#addFruit').on('show.bs.modal', function(e) {
                // console.log(e.relatedTarget);
                const $page = $(e.relatedTarget).closest('.-page');
                let id = [];
                $('input[name^="fruit_"]', $page).each(function(index, element) {
                    // element == this
                    id.push($(element).val());
                });
                $('#addFruit select.-multiple').val(id);
                $('#addFruit select.-multiple').trigger('change');
                $('#addFruit .btn-ok').data('tab', $page.attr('id'));
            });
            // -- 加入
            $('#addFruit .btn-ok').off('click.add').on('click.add', function() {
                const tab = $(this).data('tab');
                const $page = $(`#${tab}`);
                const $ul = $('ul.sortable', $page);
                const id = $('input[name="tab_id[]"]', $page).val();
                const _newFruits = $('#addFruit select.-multiple').select2('data');
                $('.sortable li', $page).addClass('del');

                _newFruits.forEach(data => {
                    if ($(`input[name^="fruit_"][value="${data.id}"]`, $page).length > 0) {
                        $(`.sortable li:has(input[name^="fruit_"][value="${data.id}"])`, $page).removeClass(
                            'del');
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
                $('.-del', page).off('click').on('click', function() {
                    $(this).closest('li').remove();
                });
            }
        </script>
    @endpush
@endOnce
