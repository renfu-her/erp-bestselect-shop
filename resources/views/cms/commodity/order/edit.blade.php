@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">新增訂單</h2>

    <form id="form1" method="post" action="">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12 col-sm-6 mb-3 ">
                </div>
            </div>
        </div>

        <div class="card shadow p-4 mb-4">
            <h6>商品清單</h6>
            <div class="table-responsive tableOverBox">
                <table class="table table-hover tableList mb-1">
                    <thead>
                    <tr>
                        <th scope="col" class="text-center">刪除</th>
                        <th scope="col">商品名稱</th>
                        <th scope="col">SKU</th>
                    </tr>
                    </thead>
                    <tbody class="-appendClone --selectedP">
                        <tr class="-cloneElem --selectedP d-none">
                            <th class="text-center">
                                <button type="button"
                                        class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <input type="hidden" name="name[]" value="">
                                <input type="hidden" name="sku[]" value="">
                            </th>
                            <td data-td="name"></td>
                            <td data-td="sku"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="d-grid mt-3">
                <button id="addProductBtn" type="button"
                        class="btn btn-outline-primary border-dashed" style="font-weight: 500;">
                    <i class="bi bi-plus-circle bold"></i> 加入商品
                </button>
            </div>

        </div>

        @error('del_error')
        <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror

        <div id="submitDiv">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">建立訂單</button>
                <a href="{{ Route('cms.purchase.index', [], true) }}" class="btn btn-outline-primary px-4"
                   role="button">返回列表</a>
            </div>
        </div>
    </form>

    {{-- 商品清單 --}}
    <x-b-modal id="addProduct" cancelBtn="false" size="modal-xl modal-fullscreen-lg-down">
        <x-slot name="title">選取商品加入採購清單</x-slot>
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
                            <input class="form-check-input" type="checkbox"
                                   value="" data-td="p_id" aria-label="選取商品">
                        </th>
                        <td data-td="name">【喜鴻嚴選】咖啡候機室(10入/盒)</td>
                        <td data-td="spec">綜合口味</td>
                        <td data-td="sku">AA2590</td>
                        <td>58</td>
                        <td>20</td>
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
            <button type="button" class="btn btn-primary btn-ok">加入採購清單</button>
        </x-slot>
    </x-b-modal>
@endsection
@once
    @push('sub-scripts')
        <script>

            if (true == isAlreadyFinalPay) {
                $('.-cloneElem.--selectedP :input').prop("disabled", true);
            }

            $('#supplier').on('change', function (e) {
                // if ("" != $('input[name=bank_cname]').val()
                //     || "" != $('input[name=bank_code]').val()
                //     || "" != $('input[name=bank_acount]').val()
                //     || "" != $('input[name=bank_numer]').val()) {
                //     if (confirm('下方已設定匯款資訊 是否根據所選廠商做變更?'))
                //     {
                //         changeRemittance();
                //     }
                // } else {
                //     changeRemittance();
                // }
            });

            //變更匯款資料
            let changeRemittance = function () {
                let supplierID = $("#supplier").val();

                let supplierItem = null;
                for (i = 0; i < supplierList.length; i++) {
                    if (supplierList[i].id == supplierID) {
                        supplierItem = supplierList[i];
                        break;
                    }
                }

                if (null != supplierItem) {
                    $('input[name=bank_cname]').val(supplierItem.bank_cname);
                    $('input[name=bank_code]').val(supplierItem.bank_code);
                    $('input[name=bank_acount]').val(supplierItem.bank_acount);
                    $('input[name=bank_numer]').val(supplierItem.bank_numer);
                }
            };

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
            const $selectedClone = $('.-cloneElem.--selectedP:first-child').clone();
            $('.-cloneElem.--selectedP.d-none').remove();

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
                let _URL = `${Laravel.apiUrl.productStyles}?page=${page}`;
                let Data = {
                    keyword: $('#addProduct .-searchBar input').val(),
                    supplier_id: $('#supplier').val()
                };

                if (!Data.supplier_id) {
                    toast.show('請先選擇採購廠商。', {type: 'warning', title: '條件未設'});
                    return false;
                } else {
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
                                (res.data).forEach(prod => {
                                    createOneProduct(prod);
                                });
                                // bind event
                                $('#addProduct .-appendClone.--product input[type="checkbox"]:not(:disabled)')
                                    .off('change').on('change', function () {
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
                            <td>${p.in_stock}</td>
                            <td>${p.safety_stock}</td>
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

