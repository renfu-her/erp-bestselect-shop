@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $sn }} 出貨審核</h2>
    <form method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>商品列表</h6>
            <div class="table-responsive tableOverBox">
                <table id="Pord_list" class="table table-striped tableList">
                    <thead>
                        <tr>
                            <th style="width:3rem;">#</th>
                            <th>商品名稱</th>
                            <th>SKU</th>
                            <th>訂購數量</th>
                            <th class="text-center" style="width: 10%">出貨數量</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ord_items_arr as $key => $ord)
                        <tr class="--prod">
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>
                                @if ($ord->combo_product_title)
                                    <span class="badge rounded-pill bg-warning text-dark">組合包</span> [
                                @else
                                    <span class="badge rounded-pill bg-success">一般</span>
                                @endif
                                {{ $ord->product_title }} @if($ord->combo_product_title) ] {{$ord->combo_product_title}} @endif
                            </td>
                            <td>{{ $ord->sku }}</td>
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
                                    <tbody class="border-top-0 -appendClone --selectedIB">
                                        @foreach ($ord->receive_depot as $rec)
                                        <tr class="-cloneElem --selectedIB">
                                            <td class="text-center">
                                                <a href="javascript:void(0)" 
                                                    data-bid="{{ $rec->inbound_id }}" data-rid="{{ $rec->id }}"
                                                    data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                                    {{ isset($delivery->close_date) ? 'disabled' : '' }}
                                                    class="icon icon-btn -del fs-5 text-danger rounded-circle border-0">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                            <td data-td="sn">{{ $rec->inbound_sn }}</td>
                                            <td data-td="depot">{{ $rec->depot_name }}</td>
                                            <td class="text-center">
                                                <input type="text" name="qty[]" value="{{ $rec->qty }}" class="form-control form-control-sm text-center" readonly>
                                            </td>
                                            <td data-td="expiry">{{ date('Y/m/d', strtotime($rec->expiry_date)) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    @if (is_null($delivery->close_date))
                                        <tfoot class="border-top-0">
                                            <tr>
                                                <td colspan="5">
                                                    <input type="hidden" class="-ord" value="{{ $ord->product_style_id }}" data-sku="{{ $ord->sku }}" 
                                                        data-title="{{ $ord->product_title }}" @if($ord->combo_product_title) data-subtitle="{{$ord->combo_product_title}}" @endif
                                                        data-qty="{{ $ord->qty }}" data-item="{{ $ord->item_id }}">
                                                    <button data-idx="{{ $key + 1 }}" type="button" class="btn -add btn-outline-primary btn-sm border-dashed w-100" style="font-weight: 500;">
                                                        <i class="bi bi-plus-circle"></i> 新增
                                                    </button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @error('error_msg')
                <div class="alert alert-danger" role="alert">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div id="submitDiv">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">送出審核</button>
                <a href="{{ Route('cms.order.detail', ['id' => $order_id]) }}" class="btn btn-outline-primary px-4" role="button">前往訂單明細</a>
            </div>
        </div>
    </form>

    {{-- 入庫清單 modal-fullscreen-lg-down --}}
    <x-b-modal id="addInbound" cancelBtn="false" size="modal-xl">
        <x-slot name="title">選擇入庫單</x-slot>
        <x-slot name="body">
            <div class="table-responsive">
                <figure class="mb-2">
                    <blockquote class="blockquote">
                        <h6 class="fs-5"></h6>
                    </blockquote>
                    <figcaption class="blockquote-footer mb-2"></figcaption>
                    <blockquote class="row mb-0">
                        <div class="col">訂購數量：0</div>
                        <div class="col text-primary">未選取數量：0</div>
                    </blockquote>
                </figure>
                <table class="table table-hover tableList">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center" style="width: 10%">選取</th>
                            <th scope="col">入庫單</th>
                            <th scope="col">倉庫</th>
                            <th scope="col">庫存</th>
                            <th scope="col">效期</th>
                            <th scope="col" style="width: 10%">預計出貨數量</th>
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
                            <td data-td="stock"></td>
                            <td data-td="expiry"></td>
                            <td data-td="qty">
                                <input type="number" value="0" min="1" max="" class="form-control form-control-sm text-center" disabled>
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
            <button type="button" class="btn btn-primary btn-ok">加入出貨審核</button>
        </x-slot>
    </x-b-modal>

    
    <!-- 刪除確認 Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="name">刪除確認</x-slot>
        <x-slot name="body">刪除後將無法復原！確認要刪除？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
        </x-slot>
    </x-b-modal>
@endsection
@once
    @push('sub-styles')
    @endpush
    @push('sub-scripts')
        <script src="{{ Asset('dist/js/deliveryAudit.js') }}"></script>
        <script>
            let addInboundModal = new bootstrap.Modal(document.getElementById('addInbound'));
            let prodPages = new Pagination($('#addInbound .-pages'));
            const DeliveryId = @json($delivery_id);
            const Del_Url = "{{ Route('cms.delivery.delete', ['subOrderId'=>$sub_order_id, 'receiveDepotId'=>'#'], true) }}".replace('#', '');
            /*** 選取 ***/
            // 入庫單
            let selectedInboundId = [];
            let selectedInbound = [
                // {
                //     id: 'ID',
                //     sn: '單號',  //新值才有
                //     depot: '倉庫',   //新值才有
                //     expiry: '效期'   //新值才有
                //     qty: '數量',
                //     new: '新值 true',
                // }
            ];
            /*** CloneElem ***/
            // clone 項目
            const $selectedClone = $(`<tr class="-cloneElem --selectedIB">
                <td class="text-center">
                    <a href="javascript:void(0)" data-bid="" data-rid=""
                        data-bs-toggle="modal" data-bs-target="#confirm-delete"
                        class="icon icon-btn -del fs-5 text-danger rounded-circle border-0">
                        <i class="bi bi-trash"></i>
                    </a>
                </td>
                <td data-td="sn"></td>
                <td data-td="depot"></td>
                <td class="text-center">
                    <input type="text" name="qty[]" value="" class="form-control form-control-sm text-center" readonly>
                </td>
                <td data-td="expiry"></td>
            </tr>`);
            // 刪除商品
            let delItemOption = {
                appendClone: '.-appendClone.--selectedIB',
                cloneElem: '.-cloneElem.--selectedIB',
                checkFn: function () {
                    // 無單不可送審
                    checkSubmit();
                }
            };
            checkSubmit();
            // 無單不可送審
            function checkSubmit() {
                let chk = true;
                $('tr.--rece').each(function (index, element) {
                    // element == this
                    chk &= $(element).find('.-cloneElem.--selectedIB').length > 0;
                });
                $('#submitDiv button[type="submit"]').prop('disabled', !chk);
            }
            /** ********* **/

            sumExportQty();

            // 刪除
            $('#confirm-delete').on('show.bs.modal', function (e) {
                $(this).find('.btn-ok').attr('href', Del_Url + $(e.relatedTarget).data('rid'));
            });

            // 加入入庫單
            $('#Pord_list tbody tr.--rece button.-add').off('click').on('click', function(e) {
                addInboundModal.show(this);
            });
            // 開啟入庫列表視窗
            $('#addInbound').on('show.bs.modal', function(e) {
                const addBtn = e.relatedTarget;
                resetAddInboundModal();
                // 取舊值
                $(addBtn).closest('table').find('tbody tr').each(function (index, element) {
                    selectedInboundId.push(Number($(element).find('a.-del').data('bid')));
                    selectedInbound.push({
                        id: Number($(element).find('a.-del').data('bid')),
                        qty: Number($(element).find('input[name="qty[]"]').val()) || 0,
                        new: false
                    });
                });
                $('#addInbound .btn-ok').data('idx', $(addBtn).data('idx'));
                getInboundList(addBtn);
            });
            // 關閉入庫列表視窗，清空值
            $('#addInbound').on('hidden.bs.modal', function(e) {
                resetAddInboundModal();
            });

            // btn - 加入入庫單
            $('#addInbound .btn-ok').off('click').on('click', function () {
                const $okBtn = $(this);
                if (!checkSelectQty()) {
                    alert('預計出貨數量不合，請檢查！');
                    return false;
                }
                // call API
                createReceiveDepot($okBtn);
            });

            // 入庫單 API
            function getInboundList(target) {
                const $input = $(target).prev('input');
                const sid = $input.val();
                const _URL = Laravel.apiUrl.inboundList;
                const Data = {
                    product_style_id: sid
                };
                let title = '';
                if ($input.data('subtitle')) {
                    title = `[ ${$input.data('title')} ] ${$input.data('subtitle')}`;
                } else {
                    title = $input.data('title');
                }
                $('#addInbound blockquote h6').text(title);
                $('#addInbound figcaption').text($input.data('sku'));
                const qty = Number($input.data('qty')) || 0;
                let un_qty = Number($input.data('a_qty')) || 0;
                un_qty = qty - un_qty;
                $('#addInbound blockquote div:first-child').text(`訂購數量：${qty}`);
                $('#addInbound blockquote div:last-child').text(`未選取數量：${un_qty}`);
                $('#addInbound .btn-ok').data({
                    'un_qty': un_qty,
                    'item_id': $input.data('item'),
                    'style_id': sid
                });

                axios.post(_URL, Data)
                    .then((result) => {
                        const res = result.data;
                        const inboData = res.data;
                        let auto_count = un_qty;
                        inboData.forEach(inbo => {
                            auto_count = createOneInbound(inbo, un_qty, auto_count);
                        });
                        $('#addInbound .-checkedNum').text(`已選擇 ${selectedInboundId.length} 筆入庫單`);
                        // bind event
                        // -- 選取
                        $('#addInbound .-appendClone.--inbound input[type="checkbox"]:not(:disabled)')
                            .off('change').on('change', function () {
                                catchCheckedInbound($(this));
                                $('#addInbound .-checkedNum').text(`已選擇 ${selectedInboundId.length} 筆入庫單`);
                            });
                        // -- 數量
                        $('#addInbound .-appendClone.--inbound input[type="number"]')
                            .off('change').on('change', function () {
                                const bid = Number($(this).closest('tr').find('input[data-td="ib_id"]').val());
                                const idx = selectedInboundId.indexOf(bid);
                                if (idx >= 0) {
                                    selectedInbound[idx].qty = Number($(this).val());
                                }
                            });
                    }).catch((err) => {
                        console.error(err);
                        toast.show('發生錯誤', { type: 'danger' });
                    });

                // 商品列表
                function createOneInbound(ib, un_qty, auto_count) {
                    const idx = selectedInboundId.indexOf(ib.inbound_id);
                    let checked = '';
                    let qty = 0;
                    let max = (un_qty < ib.qty) ? un_qty : ib.qty;
                    if (idx < 0) {  // 未選
                        qty = (auto_count < max) ? auto_count : max;
                        auto_count -= qty;
                        if (qty > 0 && max > 0) {
                            checked = 'checked';
                        }
                    } else {
                        checked = 'checked disabled';
                        qty = selectedInbound[idx].qty;
                    }
                    console.log(checked);
                    let $tr = $(`<tr>
                        <th class="text-center">
                            <input class="form-check-input" type="checkbox" ${checked}
                                value="${ib.inbound_id}" data-td="ib_id" aria-label="選取入庫單">
                        </th>
                        <td data-td="sn">${ib.inbound_sn}</td>
                        <td data-td="depot">${ib.depot_name}</td>
                        <td data-td="stock">${ib.qty}</td>
                        <td data-td="expiry">${moment(ib.expiry_date).format('YYYY/MM/DD')}</td>
                        <td data-td="qty"><input type="number" value="${qty}" min="1" max="${max}" class="form-control form-control-sm text-center" disabled></td>
                    </tr>`);
                    $('#addInbound .-appendClone.--inbound').append($tr);
                    catchCheckedInbound($tr.find('input:checkbox'));
                    return auto_count;
                }
            }

            // 加入出貨審核 API
            function createReceiveDepot($target) {
                const _URL = @json(Route('api.cms.delivery.create-receive-depot'));
                let Data = {
                    delivery_id: DeliveryId,
                    item_id: $target.data('item_id'),
                    product_style_id: $target.data('style_id'),
                    inbound_id: [],
                    qty: []
                };
                $('#addInbound .-appendClone input[type="checkbox"]:checked:not(:disabled)').each(function (index, element) {
                    // element == this
                    (Data.inbound_id).push($(element).val());
                    (Data.qty).push($(element).closest('tr').find('input[type="number"]').val());
                });
                const nth = Number($target.data('idx')) * 2;

                axios.post(_URL, Data)
                .then((result) => {
                    const res = result.data;
                    console.log(res);
                    if (res.status == 0) {
                        selectedInbound.forEach(ib => {
                            if (ib.new && !$(`#Pord_list tr.--rece:nth-child(${nth}) tr.-cloneElem.--selectedIB a.-del[data-bid="${ib.id}"]`).length) {
                                createOneSelected(ib);
                            }
                        });

                        sumExportQty();
                        // 關閉懸浮視窗
                        addInboundModal.hide();
                    } else {
                        toast.show(res.msg, { title: '加入失敗', type: 'danger' });
                    }
                }).catch((err) => {
                    console.error(err);
                    toast.show('發生錯誤', { type: 'danger' });
                });

                // 加入採購單 - 加入一個商品
                function createOneSelected(ib) {
                    const newItemOpt = {
                        ...delItemOption,
                        appendClone: `#Pord_list tr.--rece:nth-child(${nth}) .-appendClone.--selectedIB`
                    };
                    Clone_bindCloneBtn($selectedClone, function (cloneElem) {
                        cloneElem.find('input').val('');
                        cloneElem.find('.-del').data({'bid': null, 'rid': null});
                        cloneElem.find('td[data-td]').text('');
                        if (ib) {
                            cloneElem.find('.-del').data({
                                'bid': ib.id,
                                'rid': ''
                            });
                            cloneElem.find('td[data-td="sn"]').text(ib.sn);
                            cloneElem.find('td[data-td="depot"]').text(ib.depot);
                            cloneElem.find('input[name="qty[]"]').val(ib.qty);
                            cloneElem.find('td[data-td="expiry"]').text(ib.expiry);
                        }
                    }, newItemOpt);
                }
            }

            // 清空入庫 Modal
            function resetAddInboundModal() {
                selectedInbound = [];
                selectedInboundId = [];
                $('#addInbound blockquote h6, #addInbound figcaption').text('');
                $('#addInbound tbody.-appendClone.--inbound').empty();
                $('#addInbound #pageSum').text('');
                $('#addInbound .page-item:not(:first-child, :last-child)').remove();
                $('#addInbound nav').hide();
                $('#addInbound .-checkedNum').text(`已選擇 ${selectedInboundId.length} 筆入庫單`);
                $('#addInbound .-emptyData').hide();
            }

            /*** 計算數量 ***/

            // 紀錄 checked inbound
            function catchCheckedInbound($checkbox) {
                if ($checkbox.prop('disabled')) {
                    return false;
                }
                const bid = Number($($checkbox).val());
                const idx = selectedInboundId.indexOf(bid);
                const $qty = $($checkbox).closest('tr').find('input[type="number"]');
                if ($($checkbox).prop('checked')) {
                    $qty.prop({ 'disabled': false, 'required': true });
                    if (idx < 0) {
                        selectedInboundId.push(bid);
                        selectedInbound.push({
                            id: bid,
                            sn: $($checkbox).parent('th').siblings('[data-td="sn"]').text(),
                            depot: $($checkbox).parent('th').siblings('[data-td="depot"]').text(),
                            expiry: $($checkbox).parent('th').siblings('[data-td="expiry"]').text(),
                            qty: Number($qty.val()) || 0,
                            new: true
                        });
                    }
                } else {
                    $qty.prop({ 'disabled': true, 'required': false }).val(0);
                    if (idx >= 0) {
                        selectedInboundId.splice(idx, 1);
                        selectedInbound.splice(idx, 1);
                    }
                }
            }

            // 檢查數量
            function checkSelectQty() {
                let result = true;
                let sum = 0;
                $('#addInbound td[data-td="qty"] input:not(:disabled)').each(function (index, element) {
                    // element == this
                    const qty = Number($(element).val()) || 0;
                    sum += qty;
                    result &= (qty >= Number($(element).attr('min')) && qty <= Number($(element).attr('max')));
                });
                result &= (sum <= Number($('#addInbound .btn-ok').data('un_qty')));
                return result;
            }

            // 加總出貨數量
            function sumExportQty() {
                $('#Pord_list tbody tr.--prod').each(function (index, element) {
                    // element == this
                    let sum = 0;
                    $(element).next('tr.--rece').find('input[name="qty[]"]').each(function (i, el) {
                        sum += Number($(el).val()) || 0;
                    });
                    $(element).find('input[name="qty_actual[]"]').val(sum);
                    // 檢查數量 訂購>=出貨
                    const $ordInput = $(element).next('tr.--rece').find('input.-ord');
                    if ($ordInput.length) {
                        $ordInput.data('a_qty', sum);
                        if (Number($ordInput.data('qty')) <= sum) {
                            $ordInput.next('button.-add').prop('disabled', true);
                        } else {
                            $ordInput.next('button.-add').prop('disabled', false);
                        }
                    }
                });
            }
        </script>
    @endpush
@endonce
