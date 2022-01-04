@extends('layouts.main')
@section('sub-content')
    <div>
        <h2 class="mb-3">{{ $product->title }}</h2>
        <x-b-prd-navi :product="$product"></x-b-prd-navi>
    </div>

    <form id="form1" method="POST" action="">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="col-12 mb-3">
                <label class="form-label">組合包名稱 <span class="text-danger">*</span></label>
                <input class="form-control @error('title')is-invalid @enderror" name="title" type="text" maxlength="30"
                    value="{{ old('title') }}" aria-label="組合包名稱" required />
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <h6>編輯組合款式</h6>
            <div class="table-responsive tableOverBox">
                <table class="table tableList table-striped">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 100px">數量</th>
                            <th scope="col">名稱</th>
                            <th scope="col">款式</th>
                            <th scope="col">SKU</th>
                            <th scope="col" class="text-center">刪除</th>
                        </tr>
                    </thead>
                    <tbody class="-appendClone --selectedP">
                        @if (count($combos) === 0)
                            <tr class="-cloneElem --selectedP d-none">
                                <td>
                                    <input type="number" name="ps_qty[]" class="form-control form-control-sm" value="1">
                                    <input type="hidden" name="style_id[]">
                                </td>
                                <td data-td="name"></td>
                                <td data-td="spec"></td>
                                <td data-td="sku"></td>
                                <td class="text-center">
                                    <button type="button" data-sku=""
                                        class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endif
                        @foreach ($combos as $key => $combo)
                            <tr class="-cloneElem --selectedP">
                                <td>
                                    <input type="number" name="ps_qty[]" class="form-control form-control-sm"
                                        value="{{ $combo->qty }}">
                                    <input type="hidden" name="style_id[]">
                                </td>
                                <td data-td="name">{{ $combo->title }}</td>
                                <td data-td="spec">{{ $combo->spec }}</td>
                                <td data-td="sku">{{ $combo->sku }}</td>
                                <td class="text-center">
                                    <button type="button" data-sku="{{ $combo->sku }}" item_id="{{ $combo->id }}"
                                        class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-grid gap-2 mt-3">
                <button id="addProductBtn" type="button" class="btn btn-outline-primary border-dashed"
                    style="font-weight: 500;">
                    <i class="bi bi-plus-circle bold"></i> 新增款式商品
                </button>
            </div>
        </div>

        <div>
            <div class="col-auto">
                <input type="hidden" name="del_item_id">
                <button type="submit" class="btn btn-primary px-4 -checkSubmit">儲存</button>
                <a href="{{ Route('cms.product.edit-combo', ['id' => $product->id]) }}"
                    class="btn btn-outline-primary px-4" role="button">取消</a>
            </div>
        </div>
    </form>


    {{-- 商品清單 --}}
    <x-b-modal id="addProduct" cancelBtn="false" size="modal-xl modal-fullscreen-lg-down">
        <x-slot name="title">選取商品加入組合款式</x-slot>
        <x-slot name="body">
            <div class="input-group mb-3 -searchBar">
                <input type="text" class="form-control" placeholder="請輸入名稱或SKU" aria-label="搜尋條件">
                <button class="btn btn-primary" type="button">搜尋商品</button>
            </div>
            {{-- <div class="row justify-content-end mb-2">
        <div class="col-auto">
            顯示
            <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                @foreach (config('global.dataPerPage') as $value)
                    <option value="{{ $value }}">{{ $value }}</option>
                @endforeach
            </select>
            筆
        </div>
    </div> --}}
            <div class="table-responsive">
                <table class="table table-hover tableList">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">選取</th>
                            <th scope="col">商品名稱</th>
                            <th scope="col">款式</th>
                            <th scope="col">SKU</th>
                        </tr>
                    </thead>
                    <tbody class="-appendClone --product"></tbody>
                </table>
            </div>
            <div class="col d-flex justify-content-end align-items-center flex-wrap -pages"></div>
            <div class="alert alert-secondary mx-3 mb-0 -emptyData" style="display: none;" role="alert">
                查無資料！
            </div>
        </x-slot>
        <x-slot name="foot">
            <span class="me-3 -checkedNum">已選取 0 件商品</span>
            <button type="button" class="btn btn-primary btn-ok">加入組合款式</button>
        </x-slot>
    </x-b-modal>
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
            let addProductModal = new bootstrap.Modal(document.getElementById('addProduct'));
            let prodPages = new Pagination($('#addProduct .-pages'));
            /*** 選取商品 ***/
            let selectedProductSku = [];
            let selectedProduct = [];
            // clone
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
                    const item_id = $this.attr('item_id');
                    if (item_id) {
                        del_item_id.push(item_id);
                        $('input[name="del_item_id"]').val(del_item_id.toString());
                    }
                }
            };
            Clone_bindDelElem($('.-cloneElem.--selectedP .-del'), delItemOption);

            // 加入商品、搜尋商品
            $('#addProductBtn, #addProduct .-searchBar button')
                .off('click').on('click', function(e) {
                    selectedProductSku = [];
                    selectedProduct = [];
                    $('.-cloneElem.--selectedP button.-del').each(function(index, element) {
                        selectedProductSku.push($(element).attr('data-sku'));
                    });
                    if (getProductList(1) && $(this).attr('id') === 'addProductBtn') {
                        addProductModal.show();
                    }
                });

            // 商品清單 API
            function getProductList(page) {
                let _URL = `${Laravel.apiUrl.productStyles}?page=${page}`;
                let Data = {
                    keyword: $('#addProduct .-searchBar input').val(),
                    sku: $('#addProduct .-searchBar input').val()
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
                            $('#addProduct .-emptyData').hide();
                            (res.data).forEach(prod => {
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
                        <td data-td="name">${p.product_title}</td>
                        <td data-td="spec">${p.spec || ''}</td>
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
                                spec: $(element).parent('th').siblings('[data-td="spec"]').text()
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

            // btn - 加入組合款式
            $('#addProduct .btn-ok').off('click').on('click', function() {
                selectedProduct.forEach(p => {
                    if (!$(`tr.-cloneElem.--selectedP button.-del[data-sku="${p.sku}"]`).length) {
                        createOneSelected(p);
                    }
                });

                // 關閉懸浮視窗
                addProductModal.hide();

                // 加入採購單 - 加入一個商品
                function createOneSelected(p) {
                    Clone_bindCloneBtn($selectedClone, function(cloneElem) {
                        cloneElem.find('input').val('');
                        cloneElem.find('.-del').attr({
                            'data-sku': '',
                            'item_id': null
                        });
                        cloneElem.find('td[data-td]').text('');
                        if (p) {
                            cloneElem.find('td[data-td="name"]').text(p.name);
                            cloneElem.find('td[data-td="spec"]').text(p.spec || '');
                            cloneElem.find('td[data-td="sku"]').text(p.sku);
                            cloneElem.find('.-del').attr('data-sku', p.sku);
                            cloneElem.find('input[name="style_id[]"]').val(p.id);
                            cloneElem.find('input[name="ps_qty[]"]').val(1);
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
        </script>
    @endpush
@endOnce
