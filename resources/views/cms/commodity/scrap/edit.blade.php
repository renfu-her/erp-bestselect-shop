@extends('layouts.main')
@section('sub-content')
    @if ($method === 'edit')
        <h2 class="mb-3">#{{$scrapData->sn??''}} 報廢單</h2>
    @else
        <h2 class="mb-3">新增報廢單</h2>
    @endif
    @php
        $isAuditStatusApproved = App\Enums\Consignment\AuditStatus::approved() == ($scrapData->audit_status??null);
    @endphp

    <form id="form1" method="post" action="{{ $formAction }}" class="-banRedo">
        @method('POST')
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>報廢單內容</h6>
            @if ($method === 'edit')
                <dl class="row">
                    <div class="col-sm-5">
                        <dt>報廢單編號</dt>
                        <dd>{{ $scrapData->sn }}</dd>
                    </div>
                </dl>
                <dl class="row">
                    <div class="col">
                        <dt>建單人員</dt>
                        <dd>{{ $scrapData->user_name }}</dd>
                    </div>
                    <div class="col">
                        <dt>建單時間</dt>
                        <dd>{{ date('Y/m/d', strtotime($scrapData->created_at)) }}</dd>
                    </div>
                    <div class="col">
                        <dt>審核人員</dt>
                        <dd>{{ $scrapData->audit_user_name ?? '-' }}</dd>
                    </div>
                    <div class="col-sm-5">
                        <dt>審核日期</dt>
                        <dd>{{ $scrapData->audit_date? (date('Y/m/d', strtotime($scrapData->audit_date)) ?? '-'): '-' }}</dd>
                    </div>
                </dl>
            @endif
            <div class="col-12 mb-3">
                <label class="form-label">報廢單備註</label>
                <input class="form-control" type="text" value="{{$scrapData->memo ?? ''}}"
                    name="scrap_memo" placeholder="報廢單備註"
                    @if ($isAuditStatusApproved) readonly @endif>
            </div>
            <div class="mark">
                <i class="bi bi-exclamation-diamond-fill mx-1 text-warning"></i>
                <span class="bg-warning text-dark lh-1 px-1">採購</span>：報廢數量<span class="text-danger">不可大於</span>可售數量、現有庫存。
                <span class="bg-warning text-dark lh-1 px-1 ms-2">寄倉</span>：報廢數量<span class="text-danger">不可大於</span>現有庫存。
            </div>
            <div class="table-responsive tableOverBox">
                <table id="inbound_list" class="table table-striped tableList mb-1">
                    <thead>
                        <tr class="align-middle">
                            <th style="width:40px;" class="text-center">#</th>
                            <th style="width:40px;" class="text-center">刪除</th>
                            <td scope="col" class="wrap">
                                <div class="fw-bold">採購單號</div>
                                <div>入庫單</div>
                            </td>
                            <th>商品</th>
                            <th class="lh-base"><span class="bg-warning text-dark lh-1">事件</span><i class="bi bi-exclamation-diamond-fill text-warning ms-1"></i>
                                <br>倉庫
                            </th>
                            <th class="lh-1 small text-end">可售<br>數量</th>
                            <th class="lh-1 small text-end">現有<br>庫存</th>
                            <th>報廢數量 <span class="text-danger">*</span></th>
                            <th>備註</th>
                        </tr>
                    </thead>
                    <tbody class="-appendClone --selectedIB -serial-number">
                        @if(isset($scrapItemData) && 0 < count($scrapItemData))
                        @foreach ($scrapItemData as $item)
                            <tr class="-cloneElem --selectedIB">
                                <th scope="row" class="text-center"><span class="-serial-title -after"></span></th>
                                <td class="text-center">
                                    @if (!$isAuditStatusApproved)
                                        <button type="button" item_id="{{$item->item_id}}"
                                            class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                    <input type="hidden" name="item_id[]" value="{{$item->item_id}}" />
                                    <input type="hidden" name="inbound_id[]" value="{{$item->inbound_id}}" />
                                    <input type="hidden" name="product_style_id[]" value="{{$item->product_style_id}}" />
                                    <input type="hidden" name="sku[]" value="{{$item->sku}}" />
                                    <input type="hidden" name="product_title[]" value="{{$item->product_title}}" />
                                </td>
                                <td class="wrap">
                                    <div data-td="event_sn">
                                        <a href="@if(\App\Enums\Delivery\Event::purchase()->value == $item->event)
                                        {{ route('cms.purchase.edit', ['id' => $item->event_id]) }}
                                        @elseif(\App\Enums\Delivery\Event::ord_pickup()->value == $item->event)
                                        {{ route('cms.order.detail', ['id' => $item->event_id]) }}
                                        @elseif(\App\Enums\Delivery\Event::consignment()->value == $item->event)
                                        {{ route('cms.consignment.edit', ['id' => $item->event_id]) }}
                                        @endif" target="_blank">{{ $item->event_sn }}</a>
                                    </div>
                                    <div data-td="inbound_sn">{{ $item->inbound_sn ?? '-' }}</div>
                                </td>
                                <td class="wrap">
                                    <div class="lh-1 small text-secondary" data-td="sku">
                                        @if(isset($item->depot_id) && isset($item->product_style_id))
                                            <a href="{{ Route('cms.stock.stock_detail_log', ['depot_id' => $item->depot_id ?? -1, 'id' => $item->product_style_id], true) }}"
                                               class="lh-lg" target="_blank">
                                                {{ $item->sku }}
                                            </a>
                                        @else
                                            {{ $item->sku }}
                                        @endif
                                    </div>
                                    <div class="lh-base" data-td="product_title">{{$item->product_title}}</div>
                                    <div class="lh-1 small fw-light">
                                        <span class="bg-secondary text-white px-1" data-td="expiry_date">效期：{{date('Y/m/d', strtotime($item->expiry_date))}}</span>
                                    </div>
                                </td>
                                <td class="wrap">
                                    <div class="lh-base text-nowrap">
                                        <span class="bg-warning text-dark px-1" data-td="inbound_event_name">{{$item->event_name}}</span>
                                    </div>
                                    <div class="lh-1" data-td="depot_name">{{$item->depot_name}}</div>
                                </td>
                                <td class="text-end" data-td="in_stock">{{$item->in_stock}}</td>
                                <td class="text-end" data-td="qty">{{$item->remaining_qty}}</td>
                                <td class="text-center">
                                    <input type="number" name="to_scrap_qty[]" value="{{$item->to_scrap_qty}}" min="1"
                                        @switch($item->event_name)
                                            @case('採購')
                                                max="{{ min($item->in_stock, $item->remaining_qty) }}"
                                                @break
                                            @case('寄倉')
                                                max="{{ $item->remaining_qty }}"
                                                @break
                                            @default
                                        @endswitch
                                        class="form-control form-control-sm -sm" required @if ($isAuditStatusApproved) readonly @endif />
                                </td>
                                <td class="text-center">
                                    <input type="text" name="memo[]" value="{{$item->memo}}" class="form-control form-control-sm -l"
                                        @if ($isAuditStatusApproved) readonly @endif />
                                </td>
                            </tr>
                        @endforeach
                        @endif

                        <tr class="-cloneElem --selectedIB d-none">
                            <th scope="row" class="text-center"><span class="-serial-title -after"></span></th>
                            <td class="text-center">
                                <button type="button" item_id=""
                                    class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <input type="hidden" name="item_id[]" value="" />
                                <input type="hidden" name="inbound_id[]" value="" />
                                <input type="hidden" name="product_style_id[]" value="" />
                                <input type="hidden" name="sku[]" value="" />
                                <input type="hidden" name="product_title[]" value="" />
                            </td>
                            <td class="wrap">
                                <div data-td="event_sn"></div>
                                <div data-td="inbound_sn"></div>
                            </td>
                            <td class="wrap">
                                <div class="lh-1 small text-secondary" data-td="sku"></div>
                                <div class="lh-base" data-td="product_title"></div>
                                <div class="lh-1 small fw-light">
                                    <span class="bg-secondary text-white px-1" data-td="expiry_date">效期：</span>
                                </div>
                            </td>
                            <td class="wrap">
                                <div class="lh-base text-nowrap">
                                    <span class="bg-warning text-dark px-1" data-td="inbound_event_name"></span>
                                </div>
                                <div class="lh-1" data-td="depot_name"></div>
                            </td>
                            <td class="text-end" data-td="in_stock"></td>
                            <td class="text-end" data-td="qty"></td>
                            <td class="text-center">
                                <input type="number" name="to_scrap_qty[]" value="0" min="1" class="form-control form-control-sm -sm" required />
                            </td>
                            <td class="text-center">
                                <input type="text" name="memo[]" value="" class="form-control form-control-sm -l" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @if(!$isAuditStatusApproved)
                <div class="mb-3">
                    <button id="addInboundBtn" type="button"
                            class="btn btn-outline-primary btn-sm border-dashed w-100" style="font-weight: 500;">
                        <i class="bi bi-plus-circle bold"></i> 新增入庫單
                    </button>
                </div>
            @endif

            <h6 class="mb-1">其他項目</h6>

            <div class="table-responsive tableOverBox mb-3">
                <table class="table table-sm table-hover tableList mb-1">
                    <thead class="small">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">會計科目</th>
                        <th scope="col">項目</th>
                        <th scope="col">金額（單價）</th>
                        <th scope="col">數量</th>
                        <th scope="col">備註</th>
                    </tr>
                    </thead>

                    <tbody>
                    @php
                        $items = $dlv_other_items;
                    @endphp

                    @for ($i = 0; $i < 5; $i++)
                        <tr>
                            <td>{{ $i + 1 }}
                                <input type="hidden" name="back_item_id[{{ $i }}]" value="{{ $items[$i]->id ?? '' }}">
                            </td>

                            <td>
                                @if ($isAuditStatusApproved)
                                    @if (isset($items[$i]))
                                        @foreach($total_grades as $g_value)
                                            @if ($g_value['primary_id'] == old('bgrade_id.' . $i, $items[$i]->grade_id ?? ''))
                                                <input type="text" value="{{ $g_value['code'] . ' ' . $g_value['name'] }}"
                                                    class="form-control form-control-sm w-auto" readonly>
                                                <input type="hidden" value="{{ $g_value['primary_id'] }}"
                                                    name="bgrade_id[{{ $i }}]">
                                            @endif
                                        @endforeach
                                    @else
                                        <input type="text" value="" class="form-control form-control-sm w-auto" readonly>
                                    @endif
                                @else
                                    <select class="select-check form-select form-select-sm -select2 -single @error('bgrade_id.' . $i) is-invalid @enderror"
                                        name="bgrade_id[{{ $i }}]" data-placeholder="請選擇會計科目">
                                        <option value="" selected disabled>請選擇會計科目</option>
                                        @foreach($total_grades as $g_value)
                                            <option value="{{ $g_value['primary_id'] }}" {{ $g_value['primary_id'] == old('bgrade_id.' . $i, $items[$i]->grade_id ?? '') ? 'selected' : '' }}
                                            @if($g_value['grade_num'] === 1)
                                                class="grade_1"
                                                    @elseif($g_value['grade_num'] === 2)
                                                        class="grade_2"
                                                    @elseif($g_value['grade_num'] === 3)
                                                        class="grade_3"
                                                    @elseif($g_value['grade_num'] === 4)
                                                        class="grade_4"
                                                @endif
                                            >{{ $g_value['code'] . ' ' . $g_value['name'] }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </td>

                            <td>
                                <input type="text" name="btitle[{{ $i }}]"
                                       value="{{ old('btitle.' . $i, $items[$i]->product_title ?? '') }}"
                                       class="d-target form-control form-control-sm @error('btitle.' . $i) is-invalid @enderror"
                                       aria-label="項目" placeholder="請輸入項目"
                                       @if ($isAuditStatusApproved && isset($items[$i])) readonly @else disabled @endif >
                            </td>

                            <td>
                                <input type="number" name="bprice[{{ $i }}]"
                                       value="{{ old('bprice.' . $i, $items[$i]->price ?? '') }}"
                                       class="d-target r-target form-control form-control-sm @error('bprice.' . $i) is-invalid @enderror"
                                       aria-label="金額" placeholder="請輸入金額"
                                       @if ($isAuditStatusApproved && isset($items[$i])) readonly @else disabled @endif >
                            </td>

                            <td>
                                <input type="number" name="bqty[{{ $i }}]"
                                       value="{{ old('bqty.' . $i, $items[$i]->qty ?? '') }}" min="0"
                                       class="d-target r-target form-control form-control-sm @error('bqty.' . $i) is-invalid @enderror"
                                       aria-label="數量" placeholder="請輸入數量"
                                       @if ($isAuditStatusApproved && isset($items[$i])) readonly @else disabled @endif >
                            </td>

                            <td>
                                <input type="text" name="bmemo[{{ $i }}]"
                                       value="{{ old('bmemo.' . $i, $items[$i]->memo ?? '') }}"
                                       class="d-target form-control form-control-sm @error('bmemo.' . $i) is-invalid @enderror"
                                       aria-label="備註" placeholder="請輸入備註"
                                       @if ($isAuditStatusApproved && isset($items[$i])) readonly @else disabled @endif >
                            </td>
                        </tr>
                    @endfor
                    </tbody>
                </table>
            </div>

            @if(isset($scrapData))
                <fieldset class="col-12 mb-3">
                    <legend class="col-form-label">審核狀態<span class="text-danger">*</span></legend>
                    <div class="px-1">
                        @foreach (App\Enums\Consignment\AuditStatus::asArray() as $key => $val)
                            <div class="form-check form-check-inline @error('audit_status')is-invalid @enderror">
                                <label class="form-check-label">
                                    <input class="form-check-input @error('audit_status')is-invalid @enderror" name="audit_status"
                                           value="{{ $val }}" type="radio" required
                                           @if (old('audit_status', $scrapData->audit_status ?? '') == $val) checked @endif>
                                    {{ App\Enums\Consignment\AuditStatus::getDescription($val) }}
                                </label>
                            </div>
                        @endforeach
                        @error('audit_status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="col">
                            <mark class="fw-light small">
                                <i class="bi bi-exclamation-diamond-fill mx-2 text-warning">
                                </i>審核狀態改為<b> 核可 或 否決 </b>就不能再修改 除非再改回尚未審核！
                            </mark>
                        </div>
                    </div>
                </fieldset>
            @endif

            @if($errors->any())
                <div class="alert alert-danger mt-3">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
            @endif
        </div>
        <div id="submitDiv">
            <div class="col-auto">
                <input type="hidden" name="method" value="{{ $method }}" />
                <input type="hidden" name="del_item_id">
                <button type="submit" class="btn btn-primary px-4" >送出</button>
                <a href="{{ Route('cms.scrap.index', []) }}" class="btn btn-outline-primary px-4" role="button">返回明細</a>
            </div>
        </div>
    </form>


    {{-- 入庫清單 Modal --}}
    <x-b-modal id="addInbound" cancelBtn="false" size="modal-xl modal-fullscreen-xl-down modal-dialog-scrollable">
        <x-slot name="title">選擇過期入庫單</x-slot>
        <x-slot name="body">
            <div class="input-group p-3 -searchBar position-sticky bg-light" style="top:-16px;margin:-16px 0 0;">
                <input type="text" name="title" class="form-control" placeholder="請輸入商品名或SKU" aria-label="搜尋條件1">
                <input type="text" name="sn" class="form-control" placeholder="請輸入採購單號" aria-label="搜尋條件2">
                <button class="btn btn-primary" type="button">搜尋入庫單</button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover tableList mb-1">
                    <thead>
                        <tr class="align-middle">
                            <th scope="col" class="text-center" style="width: 40px">選取</th>
                            <th scope="col">採購單號</th>
                            <th scope="col">入庫單號</th>
                            <th scope="col">商品</th>
                            <th scope="col" class="lh-base"><span class="bg-warning text-dark lh-1">事件</span><br>倉庫</th>
                            <th scope="col" class="small lh-1 text-end">可售<br>數量</th>
                            <th scope="col" class="small lh-1 text-end">現有<br>庫存</th>
                        </tr>
                    </thead>
                    <tbody class="-appendClone --inbound">
                        <tr class="-cloneElem --inbound">
                            <th class="text-center">
                                <input class="form-check-input" type="checkbox"
                                    value="idx" aria-label="選取入庫單">
                            </th>
                            <td>event_sn</td>
                            <td class="wrap">
                                <div class="lh-1 small text-secondary">style_sku</div>
                                <div class="lh-base">product_title</div>
                                <div class="lh-1 small fw-light">
                                    <span class="bg-secondary text-white px-1">效期：expiry_date</span>
                                </div>
                            </td>
                            <td class="wrap">
                                <div class="lh-base text-nowrap">
                                    <span class="bg-warning text-dark px-1">inbound_event_name</span>
                                </div>
                                <div class="lh-1">depot_name</div>
                            </td>
                            <td class="text-end">in_stock</td>
                            <td class="text-end">qty</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="alert alert-secondary mx-3 mb-0 -emptyData" style="display: none;" role="alert">
                查無入庫紀錄！
            </div>
        </x-slot>
        <x-slot name="foot">
            <div class="col d-flex justify-content-end align-items-center flex-wrap -pages"></div>
            <span class="me-3 -checkedNum">已選擇 0 筆入庫單</span>
            <button type="button" class="btn btn-primary px-4 btn-ok">加入</button>
        </x-slot>
    </x-b-modal>
@endsection
@once
    @push('sub-scripts')
    <script>
        const addInboundModal = new bootstrap.Modal(document.getElementById('addInbound'));
        const consPages = new Pagination($('#addInbound .-pages'));

        /*** 選取 ***/
        // 入庫單
        let selectedInbound = [
            // {
            // inbound_id: 入庫單號,
            // product_style_id: 款式ID,
            // sku: style_sku,
            // product_title: 商品名稱,
            // event_sn: 採購單號,
            // expiry_date: 效期,
            // depot_name: 倉庫,
            // inbound_event_name: 事件,
            // in_stock: 可售數量,
            // qty: 現有數量
            // }
        ];
        let selectedInboundID = [
            // {入庫單}
        ];
        // clone
        const $selectedClone = $('.-cloneElem.--selectedIB:first-child').clone().removeClass('d-none');
        $('.-cloneElem.--selectedIB.d-none').remove();
        /*** 刪除 ***/
        let del_item_id = [];
        const delItemOption = {
            appendClone: '.-appendClone.--selectedIB',
            cloneElem: '.-cloneElem.--selectedIB',
            beforeDelFn: function ({$this}) {
                const item_id = $this.attr('item_id');
                if (item_id) {
                    del_item_id.push(item_id);
                    $('input[name="del_item_id"]').val(del_item_id.toString());
                }
            }
        };
        Clone_bindDelElem($('.-cloneElem.--selectedIB .-del'), delItemOption);
        /********/

        // 新增入庫單 btn
        $('#addInboundBtn, #addInbound .-searchBar button').off('click').on('click', function(e) {
            selectedInbound = [];
            selectedInboundID = [];
            $('.-cloneElem.--selectedIB input[name="inbound_id[]"]').each(function(index, element) {
                const inbound_id = Number($(element).val());
                selectedInboundID.push(inbound_id);
                selectedInbound.push({inbound_id});
            });
            if ($(this).attr('id') === 'addInboundBtn') {
                addInboundModal.show();
            }
            getInboundList(1);
        });

        // 過期入庫單 API
        function getInboundList(page) {
            const _URL = `${Laravel.apiUrl.expiredInboundList}?page=${page}`;
            const Data = {
                title: $('.-searchBar input[name="title"]').val() || '',
                purchase_sn: $('.-searchBar input[name="sn"]').val() || '',
                expire_day: -1
            };
            resetAddInboundModal();

            axios.post(_URL, Data)
                .then((result) => {
                    const res = result.data;console.log(res.data);
                    if (res.status === '0') {
                        const inboData = res.data;
                        inboData.forEach((inbo, i) => {
                            createOneInbound(inbo, i);
                        });

                        // bind event
                        // -- 選取
                        $('#addInbound .-appendClone.--inbound input[type="checkbox"]:not(:disabled)')
                        .off('change').on('change', function () {
                            catchCheckedInbound($(this), inboData);
                            $('#addInbound .-checkedNum').text(`已選擇 ${selectedInboundID.length} 筆入庫單`);
                        });
                        // -- 加入
                        $('#addInbound form').off('submit').submit(function () {
                            if (!$('#addInbound .-appendClone input[type="checkbox"]:checked').length) {
                                toast.show('請選擇至少 1 筆入庫單', { type: 'warning' });
                                return false;
                            }
                        });

                        // 產生分頁
                        consPages.create(res.current_page, {
                            totalData: res.total,
                            totalPages: res.last_page,
                            changePageFn: getInboundList
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
                let checked = selectedInboundID.indexOf(ib.inbound_id) < 0 ? '' : 'checked disabled';
                if ((ib.inbound_event_name === '採購' && Math.min(ib.in_stock, ib.qty) <= 0) ||
                    (ib.inbound_event_name === '寄倉' && ib.qty <= 0)) {
                    checked = checked === '' ? 'disabled' : checked;
                }

                let $tr = $(`<tr class="-cloneElem --inbound">
                    <th class="text-center">
                        <input class="form-check-input" type="checkbox" ${checked}
                            value="${i}" aria-label="選取入庫單">
                    </th>
                    <td>${ib.event_sn}</td>
                    <td>${ib.inbound_sn}</td>
                    <td class="wrap">
                        <div class="lh-1 small text-secondary">${ib.style_sku}</div>
                        <div class="lh-base">${ib.product_title}</div>
                        <div class="lh-1 small fw-light">
                            <span class="bg-secondary text-white px-1">效期：${ib.expiry_date ? moment(ib.expiry_date).format('YYYY/MM/DD') : ''}</span>
                        </div>
                    </td>
                    <td class="wrap">
                        <div class="lh-base text-nowrap">
                            <span class="bg-warning text-dark px-1">${ib.inbound_event_name}</span>
                        </div>
                        <div class="lh-1">${ib.depot_name}</div>
                    </td>
                    <td class="text-end">${ib.in_stock}</td>
                    <td class="text-end">${ib.qty}</td>
                </tr>`);
                $('#addInbound .-appendClone.--inbound').append($tr);
            }

            // 紀錄
            function catchCheckedInbound($checkbox, list) {
                const item = list[$checkbox.val()];
                const ib_id = item.inbound_id;
                const idx = selectedInboundID.indexOf(ib_id);
                if ($checkbox.prop('checked') && idx < 0) {
                    selectedInboundID.push(ib_id);
                    selectedInbound.push({
                        inbound_id: ib_id,
                        inbound_sn: item.inbound_sn,
                        product_style_id: item.product_style_id,
                        sku: item.style_sku,
                        product_title: item.product_title,
                        event_sn: item.event_sn,
                        expiry_date: moment(item.expiry_date).isValid() ? moment(item.expiry_date).format('YYYY/MM/DD') : '',
                        depot_name: item.depot_name,
                        inbound_event_name: item.inbound_event_name,
                        in_stock: item.in_stock,
                        qty: item.qty
                    });
                } else if (!$checkbox.prop('checked') && idx >= 0) {
                    selectedInbound.splice(idx, 1);
                    selectedInboundID.splice(idx, 1);
                }
            }
        }

        $('#addInbound .btn-ok').off('click').on('click', function () {
            selectedInbound.forEach(item => {
                if ($(`tr.-cloneElem.--selectedIB input[name="inbound_id[]"][value="${item.inbound_id}"]`).length === 0) {
                    createOneSelected(item);
                }
            });

            // 關閉懸浮視窗
            addInboundModal.hide();

            // 加入入庫單
            function createOneSelected(item) {
                Clone_bindCloneBtn($selectedClone, function (cloneElem) {
                    cloneElem.find('input').val('');
                    cloneElem.find('.-del').attr({
                        'item_id': null
                    });
                    cloneElem.find('td[data-td]').text('');
                    if (item) {
                        cloneElem.find('input[name="inbound_id[]"]').val(item.inbound_id);
                        cloneElem.find('input[name="product_style_id[]"]').val(item.product_style_id);
                        cloneElem.find('input[name="sku[]"]').val(item.sku);
                        cloneElem.find('input[name="product_title[]"]').val(item.product_title);

                        cloneElem.find('td div[data-td="event_sn"]').text(item.event_sn);
                        cloneElem.find('td div[data-td="inbound_sn"]').text(item.inbound_sn);
                        cloneElem.find('td [data-td="sku"]').text(item.sku);
                        cloneElem.find('td [data-td="product_title"]').text(item.product_title);
                        cloneElem.find('td [data-td="expiry_date"]').text(`效期：${item.expiry_date}`);
                        cloneElem.find('td [data-td="inbound_event_name"]').text(item.inbound_event_name);
                        cloneElem.find('td [data-td="depot_name"]').text(item.depot_name);
                        cloneElem.find('td[data-td="in_stock"]').text(item.in_stock);
                        cloneElem.find('td[data-td="qty"]').text(item.qty);
                        switch (item.inbound_event_name) {
                            case '採購':
                                cloneElem.find('input[name="to_scrap_qty[]"]').attr('max', Math.min(item.in_stock, item.qty));
                                break;
                            case '寄倉':
                                cloneElem.find('input[name="to_scrap_qty[]"]').attr('max', item.qty);
                                break;
                            default:
                                break;
                        }
                    }
                }, delItemOption);
            }
        });

        // 清空入庫 Modal
        function resetAddInboundModal() {
            $('#addInbound .-searchBar input').val('');
            $('#addInbound tbody.-appendClone.--inbound').empty();
            $('#addInbound #pageSum').text('');
            $('#addInbound .page-item:not(:first-child, :last-child)').remove();
            $('#addInbound nav').hide();
            $('#addInbound .-checkedNum').text(`已選擇 ${selectedInboundID.length} 筆入庫單`);
            $('#addInbound .-emptyData').hide();
        }

        // 關閉Modal時，清空值
        $('#addInbound').on('hidden.bs.modal', function () {
            resetAddInboundModal();
        });
    </script>
    <script>
        $(document).on('change', 'select.select-check', function() {
            if(this.value){
                $(this).parents('tr').find('.d-target').prop('disabled', false);
                $(this).parents('tr').find('.r-target').prop('required', true);
            } else {
                $(this).parents('tr').find('.d-target').prop('disabled', true);
                $(this).parents('tr').find('.r-target').prop('required', false);
            }
        });

        $.each($('select.select-check'), function(i, ele) {
            if(ele.value){
                $(ele).parents('tr').find('.d-target').prop('disabled', false);
                $(ele).parents('tr').find('.r-target').prop('required', true);
            } else {
                $(ele).parents('tr').find('.d-target').prop('disabled', true);
                $(ele).parents('tr').find('.r-target').prop('required', false);
            }
        });

        // submit
        $('#form1').on('submit', function () {
            let chk = true;
            let repeat = [];
            $('.-cloneElem.--selectedIB input[name="inbound_id[]"]').each(function(index, element) {
                const inbound_id = Number($(element).val());
                if (repeat.indexOf(inbound_id) < 0) {
                    repeat.push(inbound_id);
                } else {
                    chk = false;
                    return false;
                }
            });
            return chk;
        });
    </script>
    @endpush
@endonce


