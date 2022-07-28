@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $breadcrumb_data['sn'] }} 實際物流設定</h2>
    @if ($event === 'consignment')
        <x-b-consign-navi :id="$delivery->event_id"></x-b-consign-navi>
    @endif
    @if ($event === 'csn_order')
        <x-b-csnorder-navi :id="$delivery->event_id"></x-b-csnorder-navi>
    @endif

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
                            $combo = ('c' === $prod->prd_type) ? true : false;
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

    <form id="form_store" action="{{ Route('cms.logistic.store', [], true) }}" method="post">
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
                            <option value="{{ $ship->group_id_fk }}"
                                    @if($ship->group_id_fk == $logistic->ship_group_id) selected @endif
                                    data-cost="{{ $ship->dlv_cost }}">{{ $ship->name }}</option>
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
                            value="{{ $logistic->cost !== 0 ? $logistic->cost : 0 }}" required>
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

    <div class="card shadow p-4 mb-4">
        <h6>耗材</h6>
        <div class="table-responsive tableOverBox">
            <table id="Pord_list" class="table table-striped tableList mb-2">
                <thead>
                    <tr>
                        <th>耗材名稱-款式</th>
                        <th>SKU</th>
                        <th style="width: 10%">數量小計</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($consumWithInboundList as $consum)
                        <tr class="--prod">
                            <td>{{ $consum->product_title }}</td>
                            <td>{{ $consum->sku }}</td>
                            <td>{{ number_format($consum->total_qty) }}</td>
                        </tr>
                        <tr class="--rece">
                            <td colspan="5" class="pt-0 ps-5">
                                <table class="table mb-0 table-sm table-hover border-start border-end">
                                    <thead>
                                        <tr class="border-top-0" style="border-bottom-color:var(--bs-secondary);">
                                            @if (is_null($logistic->audit_date))
                                                <td class="text-center" style="width: 10%">刪除</td>
                                            @endif
                                            <td>入庫單</td>
                                            <td>倉庫</td>
                                            <td style="width: 10%">數量</td>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0 -appendClone --selectedIB">
                                        @foreach ($consum->groupconcat as $ib)
                                            <tr class="-cloneElem --selectedIB">
                                                @if (is_null($logistic->audit_date))
                                                    <td class="text-center">
                                                        <a href="javascript:void(0)"
                                                            data-href="{{ Route('cms.logistic.delete', ['event'=>$delivery->event, 'eventId'=>$delivery->event_id, 'consumId'=>$ib->consum_id], true) }}"
                                                            data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                                            class="icon icon-btn -del fs-5 text-danger rounded-circle border-0">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </td>
                                                @endif
                                                <td>{{ $ib->inbound_sn }}</td>
                                                <td>{{ $ib->depot_name }}</td>
                                                <td>{{ number_format($ib->qty) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if (is_null($logistic->audit_date))
            <div class="mb-3">
                <button id="addConsumeBtn" type="button" class="btn -add btn-outline-primary btn-sm border-dashed w-100" style="font-weight: 500;">
                    <i class="bi bi-plus-circle"></i> 新增
                </button>
            </div>
            <div class="col">
                <button type="button" class="btn btn-primary px-4"
                    data-bs-toggle="modal" data-bs-target="#confirm-audit">儲存耗材</button>
            </div>
        @endif
    </div>

    @if($event != \App\Enums\Delivery\Event::csn_order()->value)
        @if(isset($logistic->projlgt_order_sn))
            <div class="card shadow p-4 mb-4">
                <h6>託運單資訊(喜鴻託運單)</h6>
                <div class="col-12 mb-3">
                    <div class="form-control" readonly>
                        <button type="button" class="btn btn-link btn-sm px-4"
                                data-bs-toggle="modal" data-bs-target="#confirm-del-logistic-order">{{$logistic->projlgt_order_sn ?? ''}} 刪除喜鴻託運單</button>
                    </div>
                    @error('sn')
                    <div class="alert alert-danger mt-3">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
        @elseif(isset($depots) && isset($temps) && isset($dims))
            <div class="card shadow p-4 mb-4">
                <h6>託運單資訊(喜鴻託運單)</h6>
                <div class="col-12 mb-3">
                    <form id="form_store" action="{{ Route('cms.logistic.createLogisticOrder', [], true) }}" method="post">
                        @method('POST')
                        @csrf
                        <h7>新增喜鴻托運單</h7>
                        <div class="row">
                            <div>
                                <fieldset class="col-12 col-sm-6 mb-3">
                                    <legend class="col-form-label p-0 mb-2">寄件人 <span class="text-danger">*</span></legend>
                                    <div class="px-1 pt-1">
                                        <div class="form-check form-check-inline @error('is_true_sender')is-invalid @enderror">
                                            <label class="form-check-label">
                                                <input class="form-check-input @error('is_true_sender')is-invalid @enderror" name="is_true_sender"
                                                       value="0" type="radio" required>
                                                喜鴻國際
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline @error('is_true_sender')is-invalid @enderror">
                                            <label class="form-check-label">
                                                <input class="form-check-input @error('is_true_sender')is-invalid @enderror" name="is_true_sender"
                                                       value="1" type="radio" required>
                                                {{$send_name ?? '真實寄件人'}}
                                            </label>
                                        </div>
                                        @error('is_true_sender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">倉庫 <span class="text-danger">*</span></label>
                                <select name="depot_id" class="-select2 -single form-select" required data-placeholder="請單選">
                                    <option value="" selected disabled>請選擇</option>
                                    @foreach ($depots as $depot)
                                        <option value="{{ $depot->id }}">{{ $depot->title }}</option>
                                    @endforeach
                                </select>
                                @error('depot_id')
                                <div class="alert alert-danger mt-3">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">溫層 <span class="text-danger">*</span></label>
                                <select name="temp_id" class="-select2 -single form-select" required data-placeholder="請單選">
                                    <option value="" selected disabled>請選擇</option>
                                    @foreach ($temps as $temp)
                                        <option value="{{ $temp->id }}">{{ $temp->title }}</option>
                                    @endforeach
                                </select>
                                @error('temp_id')
                                <div class="alert alert-danger mt-3">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">溫層 <span class="text-danger">*</span></label>
                                <select name="dim_id" class="-select2 -single form-select" required data-placeholder="請單選">
                                    <option value="" selected disabled>請選擇</option>
                                    @foreach ($dims as $dim)
                                        <option value="{{ $dim->id }}">{{ $dim->volume }} x {{ $dim->weight }}</option>
                                    @endforeach
                                </select>
                                @error('dim_id')
                                <div class="alert alert-danger mt-3">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">取件日期</label>
                                <input type="date" id="pickup_date" name="pickup_date" value=""
                                       class="form-control" aria-label="取件日期" required/>
                            </div>
                            @error('pickup_date')
                            <div class="alert alert-danger mt-3">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col">
                            <input type="hidden" name="delivery_id" value="{{ $delivery->id }}">
                            <input type="hidden" name="logistic_id" value="{{ $logistic->id }}">
                            <input type="hidden" name="event" value="{{ $delivery->event }}">
                            <input type="hidden" name="event_id" value="{{ $delivery->event_id }}">
                            <button type="submit" class="btn btn-primary px-4">儲存</button>
                            @error('createOrder')
                            <div class="alert alert-danger mt-3">{{ $message }}</div>
                            @enderror
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endif

    @if(isset($projLogisticLog) && 0 < count($projLogisticLog))
        <div class="card shadow p-4 mb-4">
            <h6>喜鴻託運單產生紀錄</h6>
            <div class="table-responsive tableOverBox">
                <table class="table table-striped tableList">
                    <thead>
                    <tr>
                        <th>新增日期</th>
                        <th>行為</th>
                        <th>狀態</th>
                        <th>物流單號</th>
                        <th>上行文本</th>
                        <th>下行文本</th>
                        <th>操作人</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($projLogisticLog as $lgt_log)
                        <tr>
                            <td>{{ $lgt_log->created_at }}</td>
                            <td>{{ $lgt_log->feature }}</td>
                            <td>{{ $lgt_log->status }}</td>
                            <td>{{ $lgt_log->order_sn }}</td>
                            <td>{{ ($lgt_log->text_request) }}</td>
                            <td>{{ $lgt_log->text_response }}</td>
                            <td>{{ $lgt_log->create_user_name }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div>
        <div class="col-auto">
            <a href="{{ $returnAction }}"
                class="btn btn-outline-primary px-4" role="button">返回明細</a>
        </div>
    </div>

@if (is_null($logistic->audit_date))
{{-- Modal --}}
    <!-- 送審確認 Modal -->
    <x-b-modal id="confirm-audit">
        <x-slot name="title">審核確認</x-slot>
        <x-slot name="body">審核後將無法再做修改！確認要送審？</x-slot>
        <x-slot name="foot">
            <form action="{{ Route('cms.logistic.auditInbound', [], true) }}" method="post">
                @method('POST')
                @csrf
                <input type="hidden" name="logistic_id" value="{{ $logistic->id }}">
                <button type="submit" class="btn btn-primary">確認並送審</button>
            </form>
        </x-slot>
    </x-b-modal>

    <!-- 刪除確認 Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">刪除後將無法復原！確認要刪除？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
        </x-slot>
    </x-b-modal>

    {{-- 耗材清單 Modal --}}
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

    {{-- 入庫清單 Modal --}}
    <div class="modal fade" id="addInbound" tabindex="-1" aria-labelledby="addInboundLabel"aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addInboundLabel">選擇入庫單</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ Route('cms.logistic.storeConsum', [], true) }}" method="post">
                    @method('POST')
                    @csrf
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
@endif

    <x-b-modal id="confirm-del-logistic-order">
        <x-slot name="title">刪除委託單確認</x-slot>
        <x-slot name="body">確認刪除委託單？</x-slot>
        <x-slot name="foot">
            <form id="formDelLogisticOrder" method="post" action="{{ $DelLogisticOrderAction }}">
                @method('POST')
                @csrf
                <input type="hidden" name="event" value="{{$delivery->event}}">
                <input type="hidden" name="event_id" value="{{$delivery->event_id}}">
                <input type="hidden" name="logistic_id" value="{{$logistic->id}}">
                <input type="hidden" name="sn" value="{{$logistic->projlgt_order_sn}}">
                <button type="submit" class="btn btn-primary">確認刪除</button>
            </form>
            <form action="{{ Route('cms.logistic.auditInbound', [], true) }}" method="post">
                @method('POST')
                @csrf
                <input type="hidden" name="logistic_id" value="{{ $logistic->id }}">
            </form>
        </x-slot>
    </x-b-modal>
@endsection
@once
    @push('sub-scripts')
    <script>
        $('select[name="actual_ship_group_id"]').on('change', function () {
            const cost = $(this).children(':selected').data('cost');
            $('input[name="cost"]').val(cost);
        });
    </script>
    @if (is_null($logistic->audit_date))
    <script>
        const depot_id = @json($depot_id ?? '');
        const event = @json($event ?? '');

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
        /********/

        // 刪除
        $('#confirm-delete').on('show.bs.modal', function (e) {
            $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
        });

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
            if (depot_id) {
                Data.depot_id = depot_id;
            }
            if ('csn_order' == event) {
                Data.select_consignment = true;
            }
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
                        $('#addInbound form').off('submit').submit(function () {
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
    @endif
    @endpush
@endonce
