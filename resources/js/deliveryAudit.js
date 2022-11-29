$(function () {
    /***
     * 出貨審核
     **/

    /*** 定義 ***/
    let addInboundModal = new bootstrap.Modal(document.getElementById('addInbound'));

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
            <button type="button" href="javascript:void(0)" data-bid="" data-rid=""
                data-bs-toggle="modal" data-bs-target="#confirm-delete"
                class="icon icon-btn -del fs-5 text-danger rounded-circle border-0">
                <i class="bi bi-trash"></i>
            </button>
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
        autoRemove: false
    };

    /*** 綁定事件 ***/
    // 加入入庫單視窗
    $('#Pord_list tbody tr.--rece button.-add').off('click').on('click', function(e) {
        addInboundModal.show(this);
    });
    // 開啟入庫列表視窗
    $('#addInbound').on('show.bs.modal', function(e) {
        const addBtn = e.relatedTarget;
        resetAddInboundModal();
        // 取舊值
        $(addBtn).closest('table').find('tbody tr').each(function (index, element) {
            selectedInboundId.push(Number($(element).find('.-del').data('bid')));
            selectedInbound.push({
                id: Number($(element).find('.-del').data('bid')),
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


    /*** fn ***/

    // 入庫單 API
    function getInboundList(target) {
        const $input = $(target).prev('input');
        const sid = $input.val();
        const _URL = Laravel.apiUrl.inboundList;
        const Data = {
            product_style_id: sid
        };
        if('csn_order' == $('input[name="event"]').val()) {
            Data.select_consignment = true;
        }
        if($('input[name="depot_id"]').val()) {
            Data.depot_id = $('input[name="depot_id"]').val();
        }
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
                if (res.status === '0') {
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
                } else {
                    toast.show(res.msg, { title: '發生錯誤', type: 'danger' });
                }

            }).catch((err) => {
                console.error(err);
                toast.show('發生錯誤', { type: 'danger' });
            });

        // 入庫列表
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
            let $tr = $(`<tr>
                <th class="text-center">
                    <input class="form-check-input" type="checkbox" ${checked}
                        value="${ib.inbound_id}" data-td="ib_id" aria-label="選取入庫單">
                    <input type="hidden" name="prd_type" value="${ib.prd_type}">
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
    window.DvyCreateReceiveDepot = createReceiveDepot;
    function createReceiveDepot($target, apiUrl, deliveryId) {
        const _URL = apiUrl;
        let Data = {
            delivery_id: deliveryId,
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
            if (res.status === '0') {
                (res.data).forEach(recDep => {
                    if (!$(`#Pord_list tr.--rece:nth-child(${nth}) tr.-cloneElem.--selectedIB a.-del[data-rid="${recDep.id}"]`).length) {
                        createOneSelected(recDep);
                    }
                });

                sumExportQty();
                checkSubmit();
                // 關閉懸浮視窗
                addInboundModal.hide();
                toast.show('加入成功', { type: 'success' });
            } else {
                toast.show(res.msg, { title: '加入失敗', type: 'danger' });
            }
        }).catch((err) => {
            console.error(err);
            toast.show('發生錯誤', { type: 'danger' });
        });

        // 加入採購單 - 加入一個商品
        function createOneSelected(recDep) {
            const newItemOpt = {
                ...delItemOption,
                appendClone: `#Pord_list tr.--rece:nth-child(${nth}) .-appendClone.--selectedIB`
            };
            Clone_bindCloneBtn($selectedClone, function (cloneElem) {
                cloneElem.find('input').val('');
                cloneElem.find('.-del').data({'bid': null, 'rid': null});
                cloneElem.find('td[data-td]').text('');
                if (recDep) {
                    cloneElem.find('.-del').data({
                        'bid': recDep.inbound_id,
                        'rid': recDep.id
                    });
                    cloneElem.find('td[data-td="sn"]').text(recDep.inbound_sn);
                    cloneElem.find('td[data-td="depot"]').text(recDep.depot_name);
                    cloneElem.find('input[name="qty[]"]').val(recDep.qty);
                    cloneElem.find('td[data-td="expiry"]').text(moment(recDep.expiry_date).format('YYYY/MM/DD'));
                }
            }, newItemOpt);
        }
    }

    // 出貨數量 != 訂購數量 不可送審
    window.DvyCheckSubmit = checkSubmit;
    function checkSubmit(readonly = false) {
        let chk = true;
        $('tr.--prod').each(function (index, element) {
            // element == this
            chk &= $(element).find('input[name="qty_actual[]"').val() === $(element).find('td[data-td="o_qty"]').text().replaceAll(',','').trim();
        });
        $('#submitDiv button.-submit').prop('disabled', !chk || readonly);
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
    window.DvyCheckSelectQty = checkSelectQty;
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
    window.DvySumExportQty = sumExportQty;
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
    /** ********* **/
});
