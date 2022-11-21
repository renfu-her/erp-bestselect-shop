@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">
        @if ($method === 'create')
            新增
        @else
            編輯
        @endif 商品群組
    </h2>
    <form class="card-body" method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <x-b-form-group name="collection_name" title="商品群組名稱" required="true">
                    <input type="text" class="form-control @error('collection_name') is-invalid @enderror"
                        id="collection_name" name="collection_name"
                        value="{{ old('collection_name', $collectionData->name ?? '') }}" required aria-label="商品群組名稱" />
                </x-b-form-group>
                <p class="mark m-0"><i class="bi bi-exclamation-diamond-fill mx-2 text-warning"></i>
                    系統會自動將「商品群組名稱」代入網頁連結、標題、描述中
                    ，如需調整搜尋引擎SEO成效，可自行修改
                </p>
                <x-b-form-group name="url" title="網頁連結" required="false">
                    <input type="text" class="form-control @error('url') is-invalid @enderror" id="url"
                        name="url" value="{{ old('url', $collectionData->url ?? '') }}" aria-label="網頁連結" />
                </x-b-form-group>
                <x-b-form-group name="meta_title" title="網頁標題" required="false">
                    <input type="text" class="form-control @error('meta_title') is-invalid @enderror" id="meta_title"
                        name="meta_title" value="{{ old('meta_title', $collectionData->meta_title ?? '') }}"
                        aria-label="網頁標題" />
                </x-b-form-group>
                <x-b-form-group name="meta_description" title="網頁描述" required="false">
                    <input type="text" class="form-control @error('meta_description') is-invalid @enderror"
                        id="meta_description" name="meta_description"
                        value="{{ old('meta_description', $collectionData->meta_description ?? '') }}" aria-label="網頁描述" />
                </x-b-form-group>
                <x-b-form-group name="is_liquor" title="酒類" required="true">
                    <div class="px-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                一般
                                <input class="form-check-input @error('is_liquor') is-invalid @enderror" value="0"
                                    name="is_liquor" type="radio" @if ('0' == old('is_liquor', $collectionData->is_liquor ?? '')) checked @endif>
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                酒類
                                <input class="form-check-input @error('is_liquor') is-invalid @enderror" value="1"
                                    name="is_liquor" type="radio" @if ('1' == old('is_liquor', $collectionData->is_liquor ?? '')) checked @endif>
                            </label>
                        </div>
                    </div>
                </x-b-form-group>
            </div>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        <div class="card shadow p-4 mb-4">
            <div class="d-flex align-items-center mb-3">
                <h6 class="mb-0">商品列表</h6>

                <button type="button" id="resort" class="btn btn-outline-success btn-sm mx-2">重整排序數列</button>
            </div>
            
            <div class="table-responsive tableOverBox">
                <table class="table table-hover tableList mb-1">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">刪除</th>
                            <th scope="col">商品名稱</th>
                            <th scope="col">排序</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody class="-appendClone --selectedP">
                        @if ($method == 'create')
                            <tr class="-cloneElem --selectedP d-none">
                                <th class="text-center">
                                    <button type="button"
                                        class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <input type="hidden" name="id[]" value="">
                                    <input type="hidden" name="name[]" value="">
                                    <input type="hidden" name="type_title[]" value="">
                                    <input type="hidden" name="sku[]" value="">
                                </th>
                                <td class="wrap">
                                    <div class="lh-1 small text-nowrap">
                                        <span data-td="type_title" class="badge rounded-pill me-2"></span>
                                        <span data-td="sku" class="text-secondary"></span>
                                    </div>
                                    <div data-td="name" class="lh-base">
                                        @if (auth()->user()->can('cms.product.edit'))
                                            <a class="-text" href="" target="_blank"></a>
                                        @endif
                                    </div>
                                </td>
                                <td width="100">
                                    <input type="number" name="sort[]" value="" class="form-control form-control-sm -sm" required>
                                </td>
                            </tr>
                        @elseif(count(old('id', $dataList ?? [])) > 0)
                            @foreach ($dataList as $key => $data)
                                <tr class="-cloneElem --selectedP">
                                    <th class="text-center">
                                        <button type="button"
                                            class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <input type="hidden" name="id[]"
                                            value="{{ old('id.' . $key, $data->id ?? '') }}">
                                        <input type="hidden" name="name[]"
                                            value="{{ old('name.' . $key, $data->name ?? '') }}">
                                        <input type="hidden" name="type_title[]"
                                            value="{{ old('type_title.' . $key, $data->type_title ?? '') }}">
                                        <input type="hidden" name="sku[]"
                                            value="{{ old('sku.' . $key, $data->sku ?? '') }}">
                                    </th>
                                    <td class="wrap">
                                        <div class="lh-1 small text-nowrap">
                                            <span data-td="type_title" @class(['badge rounded-pill me-2', 
                                                'bg-warning text-dark' => old('type_title.' . $key, $data->type_title) === '組合包',
                                                'bg-success' => old('type_title.' . $key, $data->type_title) === '一般商品'])>
                                                {{ old('type_title.' . $key, $data->type_title === '組合包' ? '組合包' : '一般') }}
                                            </span>
                                            <span data-td="sku" class="text-secondary">
                                                {{ old('sku.' . $key, $data->sku ?? '') }}
                                            </span>
                                        </div>
                                        <div data-td="name" class="lh-base">
                                            @if (auth()->user()->can('cms.product.edit'))
                                                <a class="-text" href="/cms/product/edit/{{ $data->id }}" target="_blank">
                                                    {{ old('name.' . $key, $data->title ?? '') }}</a>
                                            @else
                                                {{ old('name.' . $key, $data->title ?? '') }}
                                            @endif
                                        </div>
                                    </td>
                                    <td width="100">
                                        <input type="number" name="sort[]" class="form-control form-control-sm -sm"
                                            value="{{ old('sort.' . $key, $data->sort ?? '') }}" required>
                                    </td>
                                    <td>
                                        <span class="icon -move icon-btn col-auto fs-5 text-primary rounded-circle border-0 p-0"
                                            data-bs-toggle="tooltip" title="拖曳排序">
                                            <i class="bi bi-arrows-move"></i>
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="d-grid mt-3">
                <button id="addProductBtn" type="button" class="btn btn-outline-primary border-dashed add_ship_rule"
                    style="font-weight: 500;">
                    <i class="bi bi-plus-circle bold"></i> 加入商品
                </button>
            </div>

        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ Route('cms.collection.index', [], true) }}" class="btn btn-outline-primary px-4"
                role="button">返回列表</a>
        </div>
    </form>

    <x-b-modal id="addProduct" cancelBtn="false" size="modal-xl modal-fullscreen-lg-down">
        <x-slot name="title">選取商品加入群組</x-slot>
        <x-slot name="body">
            <div class="input-group mb-3 -searchBar">
                <input type="text" class="form-control" placeholder="請輸入名稱或SKU" aria-label="搜尋條件">
                <button class="btn btn-primary" type="button">搜尋商品</button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover tableList">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">選取</th>
                            <th scope="col">商品名稱</th>
                            <th scope="col">商品形式</th>
                            <th scope="col">SKU</th>
                        </tr>
                    </thead>
                    <tbody class="-appendClone --product">
                        <tr>
                            <th class="text-center">
                                <input class="form-check-input" type="checkbox" value="" data-td="p_id"
                                    aria-label="選取商品">
                            </th>
                            <td data-td="name"></td>
                            <td data-td="type_title"></td>
                            <td data-td="sku"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col d-flex justify-content-end align-items-center flex-wrap -pages"></div>
            <div class="alert alert-secondary mx-3 mb-0 -emptyData" style="display: none;" role="alert">
                查無資料！
            </div>
        </x-slot>
        <x-slot name="foot">
            <span class="me-3 -checkedNum">已選取 0 件商品</span>
            <button type="button" class="btn btn-primary btn-ok">加入商品群組</button>
        </x-slot>
    </x-b-modal>
@endsection
@once
    @push('sub-styles')
        <style>
            /* 拖曳預覽框 */
            .-appendClone.--selectedP tr.placeholder-highlight {
                width: 100%;
                height: 60px;
                margin-bottom: .5rem;
                display: table-row;
            }
            tr.placeholder-highlight > td {
                border: none;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            let addProductModal = new bootstrap.Modal(document.getElementById('addProduct'));
            let prodPages = new Pagination($('#addProduct .-pages'));

            /*** 選取商品 ***/
            let selectedProductSku = [];
            let selectedProduct = [];
            // clone 項目
            const $selectedClone = $('.-cloneElem.--selectedP:first-child').clone();
            $('.-cloneElem.--selectedP.d-none').remove();

            /*** 刪除商品 ***/
            let del_item_id = [];
            let delItemOption = {
                appendClone: '.-appendClone.--selectedP',
                cloneElem: '.-cloneElem.--selectedP',
                beforeDelFn: function({
                    $this
                }) {
                    const id = $this.siblings('input[name="id[]"]').val();
                    if (id) {
                        del_item_id.push(id);
                        $('input[name="del_item_id"]').val(del_item_id.toString());
                    }
                },
                checkFn: function() {
                    if ($('.-cloneElem.--selectedP').length) {
                        // $('#supplier').prop('disabled', true);
                        $('button[type="submit"]').prop('disabled', false);
                    } else if (@json($method) === 'create') {
                        // $('#supplier').prop('disabled', false);
                    }
                    // 無商品不可儲存
                    if (!$('.-cloneElem.--selectedP').length) {
                        $('button[type="submit"]').prop('disabled', true);
                    }
                }
            };
            Clone_bindDelElem($('.-cloneElem.--selectedP .-del'), delItemOption);
            // 無商品不可儲存
            if (!$('.-cloneElem.--selectedP').length) {
                $('button[type="submit"]').prop('disabled', true);
            }
            bindSortableBtn();

            // 加入商品、搜尋商品
            $('#addProductBtn, #addProduct .-searchBar button')
                .off('click').on('click', function(e) {
                    selectedProductSku = [];
                    selectedProduct = [];
                    // 不檢查重複
                    // $('.-cloneElem.--selectedP input[name="sku[]"]').each(function (index, element) {
                    //     selectedProductSku.push($(element).val());
                    // });
                    if (getProductList(1) && $(this).attr('id') === 'addProductBtn') {
                        addProductModal.show();
                    }
                });

            // 商品清單 API
            function getProductList(page) {
                let _URL = `${Laravel.apiUrl.productList}?page=${page}`;
                let Data = {
                    title: $('#addProduct .-searchBar input').val(),
                    options: {
                        sku: $('#addProduct .-searchBar input').val()
                    },
                };

                $('#addProduct tbody.-appendClone.--product').empty();
                $('#addProduct #pageSum').text('');
                $('#addProduct .page-item:not(:first-child, :last-child)').remove();
                $('#addProduct nav').hide();
                $('#addProduct .-checkedNum').text(`已選取 ${selectedProductSku.length} 件商品`);

                axios.post(_URL, Data)
                    .then((result) => {
                        const res = result.data;
                        if (res.status === '0' && res.data && res.data.length) {
                            $('.-emptyData').hide();
                            (res.data)
                            .forEach(prod => {
                                createOneProduct(prod);
                            });
                            // bind event
                            $('#addProduct .-appendClone.--product input[type="checkbox"]:not(:disabled)')
                                .off('change').on('change', function() {
                                    catchCheckedProduct();
                                    $('#addProduct .-checkedNum').text(`已選取 ${selectedProductSku.length} 件商品`);
                                });

                            // 產生分頁
                            prodPages.create(res.current_page, {
                                totalData: res.total,
                                totalPages: res.last_page,
                                changePageFn: getProductList
                            });
                        } else {
                            $('#addProduct .-emptyData').show();
                        }
                    }).catch((err) => {
                        console.log(err);
                    });

                return true;

                // 商品列表
                function createOneProduct(p) {
                    let checked = (selectedProductSku.indexOf((p.sku).toString()) < 0) ? '' : 'checked disabled';
                    let $tr = $(`<tr>
                        <th class="text-center">
                            <input class="form-check-input" type="checkbox" ${checked}
                                value="${p.id}" data-td="p_id" aria-label="選取商品">
                        </th>
                        <td data-td="name">${p.title}</td>
                        <td data-td="type_title">${p.type_title || ''}</td>
                        <td data-td="sku">${p.sku}</td>
                    </tr>`);
                    $('#addProduct .-appendClone.--product').append($tr);
                }
            }

            // 紀錄 checked product
            function catchCheckedProduct() {
                $('#addProduct tbody input[data-td="p_id"]').each(function(index, element) {
                    // element == this
                    const sku = $(element).parent('th').siblings('[data-td="sku"]').text();
                    const idx = selectedProductSku.indexOf(sku);
                    if ($(element).prop('checked')) {
                        if (idx < 0) {
                            selectedProductSku.push(sku);
                            selectedProduct.push({
                                id: $(element).val(),
                                name: $(element).parent('th').siblings('[data-td="name"]').text(),
                                sku: sku,
                                type_title: $(element).parent('th').siblings('[data-td="type_title"]').text()
                            });
                        }
                    } else {
                        if (idx >= 0) {
                            selectedProductSku.splice(idx, 1);
                            selectedProduct.splice(idx, 1);
                        }
                    }

                });
            }

            // btn - 加入採購清單
            $('#addProduct .btn-ok').off('click').on('click', function() {
                selectedProduct.forEach(p => {
                    if (!$(`tr.-cloneElem.--selectedP button[data-id="${p.id}"]`).length) {
                        createOneSelected(p);
                    }
                });
                bindSortableBtn();

                // 關閉懸浮視窗
                addProductModal.hide();

                //  加入一個商品
                function createOneSelected(p) {
                    Clone_bindCloneBtn($selectedClone, function(cloneElem) {
                        cloneElem.find('input').val('');
                        // cloneElem.find('input[name="item_id[]"]').remove();
                        cloneElem.find('.-del').attr('data-id', null);

                        //使用者若有「產品頁面」編輯的權限, default setup
                        if (cloneElem.find('[data-td="name"] a').length) {
                            cloneElem.find('[data-td]').not('[data-td="name"]').text('');
                            cloneElem.find('[data-td="name"] a').text('');
                            cloneElem.find('[data-td="name"] a').attr('href', '');
                        } else {
                            cloneElem.find('[data-td]').text('');
                        }

                        cloneElem.find('.is-invalid').removeClass('is-invalid');
                        if (p) {
                            cloneElem.find('input[name="id[]"]').val(p.id);
                            cloneElem.find('input[name="name[]"]').val(`${p.name}`);
                            cloneElem.find('input[name="type_title[]"]').val(p.type_title);
                            cloneElem.find('input[name="sku[]"]').val(p.sku);

                            //使用者若有「產品頁面」編輯的權限，讓使用者可以點擊連結編輯商品
                            if (cloneElem.find('[data-td="name"] a').length) {
                                cloneElem.find('[data-td="name"] a').text(`${p.name}`);
                                cloneElem.find('[data-td="name"] a').attr('href', '/cms/product/edit/' + p
                                    .id);
                            } else {
                                cloneElem.find('[data-td="name"]').text(`${p.name}`);
                            }
                            
                            switch (p.type_title) {
                                case '組合包商品':
                                    cloneElem.find('[data-td="type_title"]').addClass('bg-warning text-dark');
                                    cloneElem.find('[data-td="type_title"]').text('組合包');
                                    break;
                                case '一般商品':
                                    cloneElem.find('[data-td="type_title"]').addClass('bg-success');
                                    cloneElem.find('[data-td="type_title"]').text('一般');
                                    break;
                                default:
                                    break;
                            }
                            cloneElem.find('[data-td="sku"]').text(p.sku);
                            cloneElem.find('input[name="sort[]"]').val(500);



                        }
                    }, delItemOption);
                }
            });
            // 關閉Modal時，清空值
            $('#addProduct').on('hidden.bs.modal', function(e) {
                selectedProductSku = [];
                selectedProduct = [];
                $('#addProduct .-searchBar input').val('');
                $('#addProduct tbody.-appendClone.--product').empty();
                $('#addProduct #pageSum').text('');
                $('#addProduct .page-item:not(:first-child, :last-child)').remove();
                $('#addProduct nav').hide();
                $('#addProduct .-checkedNum').text('已選取 0 件商品');
                $('.-emptyData').hide();
            });

            $('#resort').click(function() {
                let count = 0;
                $('.-cloneElem').each(function() {
                    count++;
                    $(this).find('input[name="sort[]"]').val(count * 10);
                })
            });

            function bindSortableBtn() {
                $('tbody.-appendClone.--selectedP.ui-sortable').sortable('destroy');

                $('tbody.-appendClone.--selectedP').sortable({
                    axis: 'y',
                    placeholder: 'placeholder-highlight',
                    connectWith: '.-appendClone.--selectedP',
                    handle: '.icon.-move',
                    cursor: 'move',
                });
                
                // 給 .placeholder-highlight 高度
                $('tbody.-appendClone.--selectedP').off('sortstart');
                $('tbody.-appendClone.--selectedP').on('sortstart', function (event, ui) {
                    (ui.helper).css('height', `${(ui.item).height()}px`);
                    (ui.placeholder).css('height', `${(ui.item).height()}px`);
                });
            }
        </script>
    @endpush
@endonce
