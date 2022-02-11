@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">新增訂單</h2>

    <form id="form1" method="post" action="">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <h6><span class="badge -step">第一步</span>添加購物車</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">訂購客戶</label>
                    <select name="orderer" class=" form-select -select2 -single" data-placeholder="請選擇訂購客戶">
                        <option value="1">陳小華</option>
                        <option value="2" selected>王曉明</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">客戶身分</label>
                    <div class="input-group-text">客戶尚未選取/員工</div>
                </div>
            </div>
            <div class="">
                <button id="addProductBtn" type="button"
                        class="btn btn-primary" style="font-weight: 500;">
                    加入商品
                </button>
            </div>
        </div>

        <div class="card shadow mb-4 -detail">
            <div class="card-header px-4 d-flex align-items-center bg-white">
                <strong class="flex-grow-1 mb-0">GGC-00455-225冷凍宅配</strong>
            </div>
            <div class="card-body px-4 py-0">
                <div class="table-responsive tableOverBox">
                    <table class="table tableList table-sm mb-0">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th scope="col">刪除</th>
                                <th scope="col">商品名稱</th>
                                <th scope="col">SKU</th>
                                <th scope="col">單價</th>
                                <th scope="col">數量</th>
                                <th scope="col">小計</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>
                                    <button type="button"
                                            class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <input type="hidden" name="name[]" value="">
                                    <input type="hidden" name="sku[]" value="">
                                </th>
                                <td><a href="#" class="-text">【春一枝】天然水果手作冰棒</a></td>
                                <td>6543</td>
                                <td>$ 100</td>
                                <td>2</td>
                                <td>$ 200</td>
                            </tr>
                            <tr>
                                <th>
                                    <button type="button"
                                            class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <input type="hidden" name="name[]" value="">
                                    <input type="hidden" name="sku[]" value="">
                                </th>
                                <td><a href="#" class="-text">紐西蘭冰河帝王鮭魚片（冷煙燻）-(200g/盒)</a></td>
                                <td>4561</td>
                                <td>$ 150</td>
                                <td>1</td>
                                <td>$ 150</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-body px-4 py-0 border-bottom">
                <div class="table-responsive tableOverBox">
                    <table class="table tableList table-sm mb-0">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th scope="col">優惠類型</th>
                                <th scope="col">優惠名稱</th>
                                <th scope="col">贈品</th>
                                <th scope="col">金額</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-body px-4">
                <dl class="row mb-0">
                    <div class="col">
                        <dt>溫層</dt>
                        <dd>冷凍</dd>
                    </div>
                    <div class="col-3">
                        <dt>消費者物流費用</dt>
                        <dd>$ 150</dd>
                    </div>
                </dl>
            </div>
        </div>

        @error('del_error')
        <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror

        <div id="submitDiv">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">建立</button>
                <a href="{{ Route('cms.order.index') }}" class="btn btn-outline-primary px-4"
                   role="button">返回列表</a>
            </div>
        </div>
    </form>

    {{-- 商品清單 --}}
    <x-b-modal id="addProduct" cancelBtn="false" size="modal-xl modal-fullscreen-lg-down">
        <x-slot name="title">選取商品加入購物車</x-slot>
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
                        <th scope="col">商品名稱</th>
                        <th scope="col">款式</th>
                        <th scope="col">SKU</th>
                        <th scope="col">加入購物車</th>
                    </tr>
                    </thead>
                    <tbody class="-appendClone --product">
                        <tr class="-cloneElem d-none">
                            <td data-td="name">【喜鴻嚴選】咖啡候機室(10入/盒)</td>
                            <td data-td="spec">綜合口味</td>
                            <td data-td="sku">AA2590</td>
                            <td>
                                <span data-bs-toggle="tooltip" title="加入購物車" data-pid=""
                                    class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-plus-circle"></i>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col d-flex justify-content-end align-items-center flex-wrap -pages"></div>
            <div class="alert alert-secondary mx-3 mb-0 -emptyData" style="display: none;" role="alert">
                查無商品！
            </div>
        </x-slot>
        <x-slot name="foot">
            <span class="me-3 -checkedNum">已添加 0 件商品</span>
        </x-slot>
    </x-b-modal>
@endsection
@once
    @push('sub-styles')
    <link rel="stylesheet" href="{{ Asset('dist/css/order.css') }}">
    <style>
        
    </style>
    @endpush
    @push('sub-scripts')
        <script>
            // 儲存前設定name
            $('#form1').submit(function(e) {
                $('input:hidden[name="supplier"]').val($('#supplier').val());
            });
        </script>
        <script>
            let addProductModal = new bootstrap.Modal(document.getElementById('addProduct'));
            let prodPages = new Pagination($('#addProduct .-pages'));
            /*** 選取商品 ***/
            let selectedProductSku = [];
            let selectedProduct = [];
            // clone 項目
            const $selectedClone = $('.-cloneElem.--selectedP.d-none').clone();
            $('.-cloneElem.d-none').remove();

            /*** 刪除商品 ***/
            let del_item_id = [];
            let delItemOption = {
                appendClone: '.-appendClone.--selectedP',
                cloneElem: '.-cloneElem.--selectedP',
                beforeDelFn: function ({$this}) {
                    const item_id = $this.siblings('input[name="item_id[]"]').val();
                    if (item_id) {
                        del_item_id.push(item_id);
                        $('input[name="del_item_id"]').val(del_item_id.toString());
                    }
                },
                checkFn: function () {
                    if ($('.-cloneElem.--selectedP').length) {
                        $('button[type="submit"]').prop('disabled', false);
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

            // 加入商品、搜尋商品
            $('#addProductBtn, #addProduct .-searchBar button')
                .off('click').on('click', function (e) {
                selectedProductSku = [];
                selectedProduct = [];
                // 檢查重複
                $('.-cloneElem.--selectedP input[name="sku[]"]').each(function (index, element) {
                    selectedProductSku.push($(element).val());
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
                    orderer_id: $('select[name="orderer"]').val()
                };

                if (!Data.orderer_id) {
                    toast.show('請先選擇訂購客戶。', {type: 'warning', title: '客戶未選取'});
                    return false;
                } else {
                    $('#addProduct tbody.-appendClone.--product').empty();
                    $('#addProduct #pageSum').text('');
                    $('#addProduct .page-item:not(:first-child, :last-child)').remove();
                    $('#addProduct nav').hide();
                    $('#addProduct .-checkedNum').text(`已添加 ${selectedProductSku.length} 件商品`);

                    axios.post(_URL, Data)
                        .then((result) => {
                            const res = result.data;
                            if (res.status === '0' && res.data && res.data.length) {
                                $('.-emptyData').hide();
                                (res.data).forEach(prod => {
                                    createOneProduct(prod);
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
                        let addBtn = '';
                        if (selectedProductSku.indexOf((p.sku).toString()) < 0) {
                            addBtn = `<span data-bs-toggle="tooltip" title="加入購物車" data-pid="${p.id}"
                                class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                <i class="bi bi-plus-circle"></i>
                            </span>`;
                            addBtn = `<button type="button" class="btn btn-outline-primary" data-pid="${p.id}">
                                <i class="bi bi-plus-circle"></i> 加入
                            </button>`;
                        } else {
                            addBtn = `<span class="text-muted">已加入</span>`;
                        }
                        let $tr = $(`<tr>
                            <td data-td="name">${p.product_title}</td>
                            <td data-td="spec">${p.spec || ''}</td>
                            <td data-td="sku">${p.sku}</td>
                            <td>${addBtn}</td>
                        </tr>`);
                        $('#addProduct .-appendClone.--product').append($tr);
                    }
                }
            }

            // 紀錄 checked product
            function catchCheckedProduct() {
                $('#addProduct tbody input[data-td="p_id"]').each(function (index, element) {
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

            // btn - 加入採購清單
            $('#addProduct .btn-ok').off('click').on('click', function () {
                selectedProduct.forEach(p => {
                    if (!$(`tr.-cloneElem.--selectedP button[data-id="${p.id}"]`).length) {
                        createOneSelected(p);
                    }
                });
                if ($('.-cloneElem.--selectedP').length) {
                    $('#supplier').prop('disabled', true);
                }

                // 關閉懸浮視窗
                addProductModal.hide();

                // 加入採購單 - 加入一個商品
                function createOneSelected(p) {
                    Clone_bindCloneBtn($selectedClone, function (cloneElem) {
                        cloneElem.find('input').val('');
                        // cloneElem.find('input[name="item_id[]"]').remove();
                        cloneElem.find('.-del').attr('data-id', null);
                        cloneElem.find('td[data-td]').text('');
                        cloneElem.find('.is-invalid').removeClass('is-invalid');
                        if (p) {
                            cloneElem.find('input[name="product_style_id[]"]').val(p.id);
                            cloneElem.find('input[name="name[]"]').val(`${p.name}-${p.spec}`);
                            cloneElem.find('input[name="sku[]"]').val(p.sku);
                            cloneElem.find('td[data-td="name"]').text(`${p.name}-${p.spec}`);
                            cloneElem.find('td[data-td="sku"]').text(p.sku);
                        }
                    }, delItemOption);
                }
            });
            // 關閉Modal時，清空值
            $('#addProduct').on('hidden.bs.modal', function (e) {
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
@endonce

