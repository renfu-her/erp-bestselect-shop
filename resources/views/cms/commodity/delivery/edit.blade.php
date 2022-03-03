@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $sn }} 出貨審核</h2>
    <form action="">
        <div class="card shadow p-4 mb-4">
            <h6>商品列表</h6>
            <div class="table-responsive tableOverBox">
                <table id="Pord_list" class="table table-striped tableList">
                    <thead>
                        <tr>
                            <th style="width:3rem;">#</th>
                            <th>商品名稱</th>
                            <th>SKU</th>
                            <th>商品類型</th>
                            <th>訂購數量</th>
                            <th class="text-center" style="width: 10%">出貨數量</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ord_items_arr as $key => $ord)
                        <tr class="--prod">
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $ord->product_title }}</td>
                            <td>{{ $ord->sku }}</td>
                            <td>(待處理)</td>
                            <td>{{ number_format($ord->qty) }}</td>
                            <td>
                                <input type="text" value="" name="qty_actual[]" class="form-control form-control-sm text-center" readonly>
                            </td>
                        </tr>
                        <tr class="--rece">
                            <td></td>
                            <td colspan="5" class="pt-0 ps-0">
                                <table class="table mb-0 table-sm table-hover border-start border-end">
                                    <thead>
                                        <tr class="border-top-0" style="border-bottom-color:var(--bs-secondary);">
                                            <td class="text-center">刪除</td>
                                            <td>入庫單</td>
                                            <td>倉庫</td>
                                            <td class="text-center" style="width: 10%">數量</td>
                                            <td>效期</td>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @foreach ($ord->receive_depot as $rec)
                                        <tr>
                                            <td class="text-center">
                                                <button class="icon icon-btn fs-5 text-danger rounded-circle border-0 -del">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                            <td>(待處理)</td>
                                            <td>{{ $rec->depot_name }}</td>
                                            <td class="text-center">
                                                <input type="text" name="qty[]" value="{{ $rec->qty }}" class="form-control form-control-sm text-center" readonly>
                                            </td>
                                            <td>{{ date('Y/m/d', strtotime($rec->expiry_date)) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="border-top-0">
                                        <tr>
                                            <td colspan="5">
                                                <input type="hidden" value="{{ $ord->id }}" 
                                                    data-title="{{ $ord->product_title }}" data-sku="{{ $ord->sku }}">
                                                <button type="button" class="btn btn-outline-primary btn-sm border-dashed w-100 -add" style="font-weight: 500;">
                                                    <i class="bi bi-plus-circle"></i> 新增
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <div id="submitDiv">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">送出審核</button>
                <a href="{{ Route('cms.order.detail', ['id' => $order_id]) }}" class="btn btn-outline-primary px-4" role="button">前往訂單明細</a>
            </div>
        </div>
    </form>
    
    {{-- 入庫清單 --}}
    <x-b-modal id="addInbound" cancelBtn="false" size="modal-xl modal-fullscreen-lg-down">
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
                            <th scope="col">效期</th>
                            <th scope="col" style="width: 10%">數量</th>
                        </tr>
                    </thead>
                    <tbody class="-appendClone --inbound">
                        <tr class="-cloneElem d-none">
                            <th>
                                <input class="form-check-input" type="checkbox"
                                   value="" data-td="ib_id" aria-label="選取入庫單">
                            </th>
                            <td data-td="sn"></td>
                            <td data-td="depot"></td>
                            <td data-td="qty"></td>
                            <td data-td="expiry"></td>
                            <td>
                                <input type="number" disabled>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col d-flex justify-content-end align-items-center flex-wrap -pages"></div>
            <div class="alert alert-secondary mx-3 mb-0 -emptyData" style="display: none;" role="alert">
                查無入庫紀錄！
            </div>
        </x-slot>
        <x-slot name="foot">
            <span class="me-3 -checkedNum">已選擇 0 筆入庫單</span>
        </x-slot>
    </x-b-modal>
@endsection
@once
    @push('sub-styles')
    @endpush
    @push('sub-scripts')
        <script>
            let addInboundModal = new bootstrap.Modal(document.getElementById('addInbound'), {
                backdrop: 'static',
                keyboard: false
            });
            let prodPages = new Pagination($('#addInbound .-pages'));
            /*** 選取 ***/
            // 入庫單
            let selectedInboundId = [];
            let selectedInbound = [
                // {
                //     id: 'ID',
                //     sn: '單號',
                //     depot: '倉庫',
                //     qty: '數量',
                //     expiry: '效期'
                // }
            ];
            /** ********* **/

            sumExportQty();
            
            // 加入入庫單
            $('#Pord_list tbody tr.--rece button.-add').off('click').on('click', function(e) {
                addInboundModal.show(this);
            });
            // 開啟入庫列表視窗
            $('#addInbound').on('show.bs.modal', function(e) {
                selectedInboundId = selectedInbound = [];
                getInboundList(e.relatedTarget);
            });
            // 入庫單 API
            function getInboundList(target) {
                const $input = $(target).prev('input');
                const id = $input.val();
                const _URL = `${Laravel.apiUrl.inboundList}/${id}`;
                console.log(_URL);
                resetAddInboundModal();
                $('#addInbound blockquote h6').text(`${$input.data('title')}`);
                $('#addInbound figcaption').text($input.data('sku'));

                axios.get(_URL)
                    .then((result) => {
                        const res = result.data;
                        const inboData = res.data;
                        console.log(inboData);
                        inboData.forEach(inbo => {
                            if (selectedInboundId.indexOf(inbo.inbound_id) < 0) {
                                createOneInbound(inbo);
                            }
                        });
                    }).catch((err) => {
                        
                    });
                    
                // 商品列表
                function createOneInbound(ib) {
                    let $tr = $(`<tr>
                        <th class="text-center">
                            <input class="form-check-input" type="checkbox"
                                value="${ib.inbound_id}" data-td="ib_id" aria-label="選取入庫單">
                        </th>
                        <td data-td="sn">(待處理)</td>
                        <td data-td="depot">${ib.depot_name}</td>
                        <td data-td="stock">${ib.qty}</td>
                        <td data-td="expiry">${moment(ib.expiry_date).format('YYYY/MM/DD')}</td>
                        <td data-td="qty"><input type="number" class="form-control form-control-sm text-center" disabled></td>
                    </tr>`);
                    $('#addInbound .-appendClone.--inbound').append($tr);
                }
            }
            // 清空入庫 Modal
            function resetAddInboundModal() {
                $('#addInbound blockquote h6, #addInbound figcaption').text('');
                $('#addInbound tbody.-appendClone.--inbound').empty();
                $('#addInbound #pageSum').text('');
                $('#addInbound .page-item:not(:first-child, :last-child)').remove();
                $('#addInbound nav').hide();
                $('#addProduct .-checkedNum').text(`已選擇 ${selectedInboundId.length} 筆入庫單`);
                $('#addProduct .-emptyData').hide();
            }

            /*** 計算 ***/
            // 加總出貨數量
            function sumExportQty() {
                $('#Pord_list tbody tr.--prod').each(function (index, element) {
                    // element == this
                    let sum = 0;
                    $(element).next('tr.--rece').find('input[name="qty[]"]').each(function (i, el) {
                        sum += Number($(el).val()) || 0;
                    });
                    $(element).find('input[name="qty_actual[]"]').val(sum);
                });
            }
        </script>
    @endpush
@endonce