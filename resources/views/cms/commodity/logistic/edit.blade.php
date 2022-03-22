@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $delivery->sn }} 實際物流設定</h2>
    @error('error_msg')
    <div class="alert alert-danger" role="alert">
        {{ $message }}
    </div>
    @enderror

    <div class="card shadow p-4 mb-4">
        <h6>出貨商品列表</h6>
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th>商品名稱</th>
                        <th>類型</th>
                        <th>單價</th>
                        <th>數量</th>
                        <th>小計</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($deliveryList as $prod)
                        @php
                            $combo = $prod->product_title !== $prod->rec_product_title
                        @endphp
                        <tr>
                            <td>
                                @if ($combo)
                                    <span class="badge rounded-pill bg-warning text-dark">組合包</span> [
                                @else
                                    <span class="badge rounded-pill bg-success">一般</span>
                                @endif
                                {{ $prod->product_title }} @if($combo) ] {{$prod->rec_product_title}} @endif
                            </td>
                            <td>商品</td>
                            <td>${{ number_format($prod->price) }}</td>
                            <td>{{ number_format($prod->send_qty) }}</td>
                            <td>${{ number_format($prod->price * $prod->send_qty) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <form action="{{ Route('cms.logistic.store', [], true) }}" method="post">
        @method('POST')
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>物流基本資料</h6>
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label">物流 <span class="text-danger">*</span></label>
                    <select name="actual_ship_group_id" class="-select2 -single form-select" required data-placeholder="請單選">
                        <option value="" selected disabled>請選擇</option>
                        @foreach ($shipmentGroup as $ship)
                            <option value="{{ $ship->id }}">{{ $ship->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label class="form-label">包裹編號</label>
                    <input class="form-control" name="package_sn" value="{{ $logistic->package_sn ?? '' }}" type="text" placeholder="請輸入物流包裹編號" aria-label="包裹編號">
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label class="form-label">成本 <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                        <input type="number" class="form-control" name="cost" min="0" placeholder="請輸入成本"
                            value="{{ $logistic->cost !== 0 ? $logistic->cost : $defDeliveryCost }}">
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">備註</label>
                    <textarea class="form-control" name="memo" placeholder="備註">{{ $logistic->memo ?? '' }}</textarea>
                </div>
            </div>
            <div class="col">
                <input type="hidden" name="logistic_id" value="{{ $logistic->id }}">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
            </div>
        </div>
    </form>

    <form action="{{ Route('cms.logistic.auditInbound', [], true) }}" method="post">
        @method('POST')
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>耗材</h6>
            <div class="table-responsive tableOverBox">
                <table id="Pord_list" class="table table-striped tableList mb-2">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 10%">刪除</th>
                            <th>耗材名稱</th>
                            <th>款式</th>
                            <th>SKU</th>
                            <th style="width: 10%">數量小計</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="--prod">
                            <td class="text-center">
                                <button href="javascript:void(0)" type="button"
                                    data-bid="" data-rid="}"
                                    data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                    class="icon icon-btn -del fs-5 text-danger rounded-circle border-0">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                            <td>耗材</td>
                            <td>60x55x40</td>
                            <td>A123</td>
                            <td>1</td>
                        </tr>
                        <tr class="--rece">
                            <td colspan="5" class="pt-0 ps-5">
                                <table class="table mb-0 table-sm table-hover border-start border-end">
                                    <thead>
                                        <tr class="border-top-0" style="border-bottom-color:var(--bs-secondary);">
                                            <td>入庫單</td>
                                            <td>倉庫</td>
                                            <td class="text-center" style="width: 10%">數量</td>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0 -appendClone --selectedIB">
                                        <tr class="-cloneElem --selectedIB">
                                            <td data-td="sn"></td>
                                            <td data-td="depot"></td>
                                            <td data-td="qty"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @if (is_null($logistic->audit_date))
                <div class="mb-3">
                    <button id="addConsumeBtn" type="button" class="btn -add btn-outline-primary btn-sm border-dashed w-100" style="font-weight: 500;">
                        <i class="bi bi-plus-circle"></i> 新增
                    </button>
                </div>
            @endif

            <div class="col">
                <input type="hidden" name="logistic_id" value="{{ $logistic->id }}">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
            </div>
        </div>
    </form>

    <div>
        <div class="col-auto">
            <a href=""
                class="btn btn-outline-primary px-4" role="button">返回明細</a>
        </div>
    </div>


    {{-- 耗材清單 --}}
    <x-b-modal id="addConsume" cancelBtn="false" size="modal-xl modal-fullscreen-lg-down">
        <x-slot name="title">選擇耗材</x-slot>
        <x-slot name="body">
            <div class="table-responsive">
                <table class="table table-hover tableList">
                    <thead>
                        <tr>
                            <th scope="col">耗材名稱</th>
                            <th scope="col">款式</th>
                            <th scope="col">SKU</th>
                            <th scope="col">選擇</th>
                        </tr>
                    </thead>
                    <tbody class="-appendClone --consume">
                        <tr class="-cloneElem d-none">
                            <td></td>
                            <td></td>
                            <td>$0</td>
                            <td>
                                <button type="button" class="btn btn-outline-primary -add" data-idx="">
                                    <i class="bi bi-plus-circle"></i> 選擇
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col d-flex justify-content-end align-items-center flex-wrap -pages"></div>
            <div class="alert alert-secondary mx-3 mb-0 -emptyData" style="display: none;" role="alert">
                查無耗材！
            </div>
        </x-slot>
    </x-b-modal>

    {{-- 入庫清單 --}}
    <div class="modal fade" id="addInbound" tabindex="-1" aria-labelledby="addInboundLabel"aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addInboundLabel">選擇入庫單</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ Route('cms.logistic.storeConsum', [], true) }}" method="post">
                    <div class="modal-body">
                        <div class="table-responsive">
                            <figure class="mb-2">
                                <blockquote class="blockquote">
                                    <h6 class="fs-5"></h6>
                                </blockquote>
                                <figcaption class="blockquote-footer mb-2"></figcaption>
                            </figure>
                            <table class="table table-hover tableList">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center" style="width: 10%">選取</th>
                                        <th scope="col">入庫單</th>
                                        <th scope="col">倉庫</th>
                                        <th scope="col">庫存</th>
                                        <th scope="col" style="width: 10%">預計使用數量</th>
                                    </tr>
                                </thead>
                                <tbody class="-appendClone --inbound">
                                    <tr class="-cloneElem d-none">
                                        <th class="text-center">
                                            <input class="form-check-input" type="checkbox" name="inbound_id[]"
                                               value="" aria-label="選取入庫單">
                                        </th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>
                                            <input type="number" name="qty[]" value="0" min="1" max="" class="form-control form-control-sm text-center" disabled>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-secondary mx-3 mb-0 -emptyData" style="display: none;" role="alert">
                            查無入庫紀錄！
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="logistic_id" value="{{ $logistic->id }}">
                        <span class="me-3 -checkedNum">已選擇 0 筆入庫單</span>
                        <button type="button" class="btn btn-secondary" data-bs-target="#addConsume" data-bs-toggle="modal" data-bs-dismiss="modal">返回列表</button>
                        <button type="submit" class="btn btn-primary btn-ok">加入</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- <x-b-modal id="addInbound" cancelBtn="false" size="modal-xl">
        <x-slot name="title">選擇入庫單</x-slot>
        <x-slot name="body">
            <div class="table-responsive">
                <figure class="mb-2">
                    <blockquote class="blockquote">
                        <h6 class="fs-5"></h6>
                    </blockquote>
                    <figcaption class="blockquote-footer mb-2"></figcaption>
                </figure>
                <table class="table table-hover tableList">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center" style="width: 10%">選取</th>
                            <th scope="col">入庫單</th>
                            <th scope="col">倉庫</th>
                            <th scope="col">庫存</th>
                            <th scope="col" style="width: 10%">預計使用數量</th>
                        </tr>
                    </thead>
                    <tbody class="-appendClone --inbound">
                        <tr class="-cloneElem d-none">
                            <th class="text-center">
                                <input class="form-check-input" type="checkbox"
                                   value="" data-td="idx" aria-label="選取入庫單">
                            </th>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>
                                <input type="number" value="0" min="1" max="" class="form-control form-control-sm text-center" disabled>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="alert alert-secondary mx-3 mb-0 -emptyData" style="display: none;" role="alert">
                查無入庫紀錄！
            </div>
        </x-slot>
        <x-slot name="foot">
            <span class="me-3 -checkedNum">已選擇 0 筆入庫單</span>
            <button class="btn btn-secondary" data-bs-target="#addConsume" data-bs-toggle="modal" data-bs-dismiss="modal">返回列表</button>
            <button type="button" class="btn btn-primary btn-ok">加入</button>
        </x-slot>
    </x-b-modal> --}}
@endsection
@once
    @push('sub-scripts')
    <script>
        let addConsumeModal = new bootstrap.Modal(document.getElementById('addConsume'));
        let addInboundModal = new bootstrap.Modal(document.getElementById('addInbound'));
        let consPages = new Pagination($('#addConsume .-pages'));
        // clone 項目
        $('#addConsume -cloneElem d-none').remove();
        /*** 選取 ***/
        // 耗材
        let selectedConsume = {
            // sid: '樣式ID',
            // name: '商品名稱',
            // spec: '樣式',
            // sku: 'SKU',
        };
        // 入庫單
        let selectedInbound = [
            // index
        ];

        /*** 耗材 ***/
        // 新增 btn
        $('#addConsumeBtn').off('click').on('click', function(e) {
            addConsumeModal.show();
        });

        // 開啟耗材列表視窗
        $('#addConsume').on('show.bs.modal', function() {
            getConsumeList(1);
        });

        // 耗材款式 API
        function getConsumeList(page) {
            const _URL = `${Laravel.apiUrl.productStyles}?page=${page}`;
            const Data = {
                consume: 1
            };
            resetAddConsumeModal();

            axios.post(_URL, Data)
                .then((result) => {
                    const res = result.data;
                    const prodData = res.data;
                    if (res.status === '0' && prodData && prodData.length) {
                        $('#addConsume .-emptyData').hide();
                        prodData.forEach((prod, i) => {
                            createOneConsume(prod, i);
                        });

                        // bind 選擇btn
                        $('#addConsume .-appendClone.--consume .-add').on('click', function() {
                            const idx = Number($(this).attr('data-idx'));
                            setConsume(prodData[idx]);

                            // 關閉耗材選擇視窗
                            addConsumeModal.hide();
                            // 開啟入庫單選擇視窗
                            addInboundModal.show();
                        });

                        // 產生分頁
                        prodPages.create(res.current_page, {
                            totalData: res.total,
                            totalPages: res.last_page,
                            changePageFn: getProductList
                        });
                    } else {
                        $('#addConsume .-emptyData').show();
                    }
                }).catch((err) => {

                });

            // 耗材列表
            function createOneConsume(p, i) {
                let $tr = $(`<tr>
                    <td>${p.product_title}</td>
                    <td>${p.spec || ''}</td>
                    <td>${p.sku}</td>
                    <td>
                        <button type="button" class="btn btn-outline-primary -add" data-idx="${i}">
                            <i class="bi bi-plus-circle"></i> 選擇
                        </button>
                    </td>
                </tr>`);
                $('#addConsume .-appendClone.--consume').append($tr);
            }
            // 選擇耗材
            function setConsume(c) {
                selectedConsume = {
                    sid: c.id,
                    name: c.product_title,
                    spec: c.spec,
                    sku: c.sku
                };
            }
        }

        // 清空耗材 Modal
        function resetAddConsumeModal() {
            $('#addConsume tbody.-appendClone.--consume').empty();
            $('#addConsume #pageSum').text('');
            $('#addConsume .page-item:not(:first-child, :last-child)').remove();
            $('#addConsume nav').hide();
            $('.-emptyData').hide();
        }

        /*** 入庫單 ***/
        // 開啟入庫單列表視窗
        $('#addInbound').on('show.bs.modal', function(e) {
            getInboundList();
        });

        // 入庫單 API
        function getInboundList() {
            const _URL = Laravel.apiUrl.inboundList;
            const Data = {
                product_style_id: selectedConsume.sid
            };
            resetAddInboundModal();
            $('#addInbound blockquote h6').text(`${selectedConsume.name} [${selectedConsume.spec}]`);
            $('#addInbound figcaption').text(selectedConsume.sku);

            axios.post(_URL, Data)
                .then((result) => {
                    const res = result.data;
                    if (res.status === '0') {
                        const inboData = res.data;
                        inboData.forEach((inbo, i) => {
                            createOneInbound(inbo, i);
                        });

                        // bind event
                        // -- 選取
                        $('#addInbound .-appendClone.--inbound input[type="checkbox"]').off('change').on('change', function () {
                            catchCheckedInbound($(this));
                            $('#addInbound .-checkedNum').text(`已選擇 ${selectedInbound.length} 筆入庫單`);
                        });
                        // -- 加入
                        $('#addInbound form').submit(function () {
                            if (!$('#addInbound .-appendClone input[type="checkbox"]:checked').length) {
                                toast.show('請選擇至少 1 筆入庫單', { type: 'warning' });
                                return false;
                            }
                        });
                    } else {
                        toast.show(res.msg, { title: '發生錯誤', type: 'danger' });
                    }

                }).catch((err) => {
                    console.error(err);
                    toast.show('發生錯誤', { type: 'danger' });
                });

            // 入庫列表
            function createOneInbound(ib, i) {
                let $tr = $(`<tr>
                    <th class="text-center">
                        <input class="form-check-input" type="checkbox" name="inbound_id[]"
                            value="${ib.inbound_id}" aria-label="選取入庫單">
                    </th>
                    <td>${ib.inbound_sn}</td>
                    <td>${ib.depot_name}</td>
                    <td>${ib.qty}</td>
                    <td><input type="number" name="qty[]" value="" min="1" max="${ib.qty}" class="form-control form-control-sm text-center" disabled></td>
                </tr>`);
                $('#addInbound .-appendClone.--inbound').append($tr);
            }
        }

        // 清空入庫 Modal
        function resetAddInboundModal() {
            selectedInbound = [];
            $('#addInbound blockquote h6, #addInbound figcaption').text('');
            $('#addInbound tbody.-appendClone.--inbound').empty();
            $('#addInbound .-checkedNum').text(`已選擇 ${selectedInbound.length} 筆入庫單`);
            $('#addInbound .-emptyData').hide();
        }

        // 紀錄 checked inbound
        function catchCheckedInbound($checkbox) {
            if ($checkbox.prop('disabled')) {
                return false;
            }
            const value = $($checkbox).val();
            const idx = selectedInbound.indexOf(value);
            const $qty = $($checkbox).closest('tr').find('input[type="number"]');
            if ($($checkbox).prop('checked')) {
                $qty.prop({ 'disabled': false, 'required': true });
                if (idx < 0) {
                    selectedInbound.push(value);
                }
            } else {
                $qty.prop({ 'disabled': true, 'required': false }).val('');
                if (idx >= 0) {
                    selectedInbound.splice(idx, 1);
                }
            }
        }

    </script>
    @endpush
@endonce
