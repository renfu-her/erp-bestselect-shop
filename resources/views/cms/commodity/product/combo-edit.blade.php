@extends('layouts.main')
@section('sub-content')
    <div>
        <h2 class="mb-3">{{ $product->title }}</h2>
        <x-b-prd-navi :product="$product"></x-b-prd-navi>
    </div>

    <form id="form1" method="POST" action="">
        @csrf
        <div class="card shadow p-4 mb-4">
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
                    <tbody class="-appendClone">
                        @foreach ($combos as $key => $combo)
                            <tr class="-cloneElem">
                                <td>
                                    <input type="number" name="" class="form-control form-control-sm" value="{{ $combo->qty }}">
                                </td>
                                <td>{{ $combo->title }}</td>
                                <td>{{ $combo->spec }}</td>
                                <td>{{ $combo->sku }}</td>
                                <td class="text-center">
                                    <button type="button"
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
                <button type="submit" class="btn btn-primary px-4 -checkSubmit">儲存</button>
                <a href="{{ Route('cms.product.edit-combo', ['id' => 1]) }}" class="btn btn-outline-primary px-4"
                    role="button">取消</a>
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
                            <th scope="col">庫存數量</th>
                            <th scope="col">預扣庫存量</th>
                        </tr>
                    </thead>
                    <tbody class="-appendClone --product">
                        <tr>
                            <th class="text-center">
                                <input class="form-check-input" type="checkbox" value="" data-td="p_id" aria-label="選取商品">
                            </th>
                            <td data-td="name">【喜鴻嚴選】咖啡候機室(10入/盒) </td>
                            <td data-td="spec">綜合口味</td>
                            <td data-td="sku">AA2590</td>
                            <td>58</td>
                            <td>20</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col d-flex justify-content-end align-items-center flex-wrap">
                <div id="pageSum" class="me-1">共 1 頁（共 0 筆資料）</div>
                {{-- 頁碼 --}}
                <div class="d-flex justify-content-center">
                    <nav>
                        <ul class="pagination">
                            <li class="page-item disabled">
                                <button type="button" class="page-link" aria-label="Previous">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                            </li>
                            <li class="page-item disabled">
                                <button type="button" class="page-link" aria-label="Next">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
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
            /*** 選取商品 ***/
            let selectedProductId = [];
            let selectedProduct = [];
            // clone

            // init

            // 加入商品、搜尋商品
            $('#addProductBtn, #addProduct .-searchBar button')
                .off('click').on('click', function(e) {
                    selectedProductId = [];
                    selectedProduct = [];
                    $('.-cloneElem.--selectedP input[name="ps_id"]').each(function(index, element) {
                        selectedProductId.push($(element).val());
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
                $('#addProduct .-checkedNum').text(`已選取 ${selectedProductId.length} 件商品`);

                axios.post(_URL, Data)
                    .then((result) => {
                        const res = result.data;
                        if (res.status === '0' && res.data && res.data.length) {
                            $('.-emptyData').hide();
                            (res.data).forEach(prod => {
                                createOneProduct(prod);
                            });
                            // bind event
                            $('#addProduct .-appendClone.--product input[type="checkbox"]:not(:disabled)')
                                .off('change').on('change', function() {
                                    // catchCheckedProduct();
                                    $('#addProduct .-checkedNum').text(`已選取 ${selectedProductId.length} 件商品`);
                                });

                            // initPages(res.total, res.last_page, res.current_page);
                        } else {
                            $('#addProduct .-emptyData').show();
                        }
                    }).catch((err) => {
                        console.log(err);
                    });

                return true;


                // 商品列表
                function createOneProduct(p) {
                    let checked = (selectedProductId.indexOf((p.id).toString()) < 0) ? '' : 'checked disabled';
                    let $tr = $(`<tr>
                        <th class="text-center">
                            <input class="form-check-input" type="checkbox" ${checked}
                                value="${p.id}" data-td="p_id" aria-label="選取商品">
                        </th>
                        <td data-td="name">${p.product_title}</td>
                        <td data-td="spec">${p.spec || ''}</td>
                        <td data-td="sku">${p.sku}</td>
                        <td>${p.in_stock}</td>
                        <td>${p.safety_stock}</td>
                    </tr>`);
                    $('#addProduct .-appendClone.--product').append($tr);
                }
            }

            // 紀錄 checked product
            function catchCheckedProduct() {
                $('#addProduct tbody input[data-td="p_id"]').each(function(index, element) {
                    // element == this
                    const pid = $(element).val();
                    const idx = selectedProductId.indexOf(pid);
                    if ($(element).prop('checked')) {
                        if (idx < 0) {
                            selectedProductId.push(pid);
                            selectedProduct.push({
                                id: pid,
                                name: $(element).parent('th').siblings('[data-td="name"]').text(),
                                sku: $(element).parent('th').siblings('[data-td="sku"]').text(),
                                spec: $(element).parent('th').siblings('[data-td="spec"]').text()
                            });
                        }
                    } else {
                        if (idx >= 0) {
                            selectedProductId.splice(idx, 1);
                            selectedProduct.splice(idx, 1);
                        }
                    }

                });
            }

            // 新增規格
            $('.-newSpec').off('click').on('click', function() {
                Clone_bindCloneBtn($cloneSpec, function($c_s) {
                    $c_s.find('input, select').val('');
                    $c_s.find('input, select, button').prop('disabled', false);
                    $c_s.find(`${Items.clone}:nth-child(n+2), select.-single + input:hidden`).remove();
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
                    $(element).find('select.-single.-select2, select.-single + input:hidden')
                        .attr('name', `'spec${index}`);
                    $(element).find(`${Items.clone} input`).attr('name', `item${index}[]`);
                });
            });
        </script>
    @endpush
@endOnce
