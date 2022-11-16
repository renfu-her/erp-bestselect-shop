@extends('layouts.main')
@section('sub-content')
    @if ($method === 'edit')
        <h2 class="mb-3">#{{ $purchaseData->purchase_sn }} 採購單</h2>
        <x-b-pch-navi :id="$id" :purchaseData="$purchaseData"></x-b-pch-navi>
    @else
        <h2 class="mb-3">新增採購單</h2>
    @endif

    @php
        $hasCreatedFinalPayment = $hasCreatedFinalPayment ?? false;
        $purchaseData = $purchaseData ?? null;
    @endphp

    @if ($method === 'edit')
        @php
            $hasLogistics = $purchaseData->logistics_price !== 0 || !empty($purchaseData->logistics_memo);
            $audit_approved = $purchaseData->audit_status == App\Enums\Consignment\AuditStatus::approved()->value;
        @endphp
    @endif

    <form id="form1" method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        @error('id')
        <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror

        @if ($method === 'edit')
            <input type='hidden' name='id' value="{{ old('id', $id) }}"/>

            <div class="card shadow p-4 mb-4">
                <h6>基本資訊</h6>
                <div class="row">
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">新增人員</label>
                        <div class="form-control" readonly>{{ empty($purchaseData->user_name) ? '-' : $purchaseData->user_name }}</div>
                    </div>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">狀態</label>
                        <div class="form-control" readonly> {{ App\Enums\Consignment\AuditStatus::getDescription($purchaseData->audit_status) }}</div>
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">入庫人員</label>
                        <div class="form-control" readonly>{{ empty($inbound_names) ? '-' : $inbound_names }}</div>
                    </div>
                    <fieldset class="col-12 col-sm-6 mb-3">
                        <legend class="col-form-label p-0 mb-2">課稅別 <span class="text-danger">*</span></legend>
                        <div class="px-1 pt-1">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="tax" type="radio" value="1" required
                                           @if ($hasCreatedFinalPayment) disabled @endif
                                           @if (1 === $purchaseData->has_tax ?? '') checked @endif>
                                    應稅
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="tax" type="radio" value="0" required
                                           @if ($hasCreatedFinalPayment) disabled @endif
                                           @if (0 === $purchaseData->has_tax ?? '') checked @endif>
                                    免稅
                                </label>
                            </div>
                        </div>
                    </fieldset>

                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">相關單號</label>
                        @if (isset($relation_order))
                            @foreach ($relation_order as $value)
                                <div><a href="{{ $value->url }}">{{ $value->sn }}</a></div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="card shadow p-4 mb-4">
            <h6>廠商資訊</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">採購廠商 <span class="text-danger">*</span></label>
                    @if ($method === 'edit')
                        <div class="form-control" readonly>
                            {{ $purchaseData->supplier_name }}@if ($purchaseData->supplier_nickname)（{{ $purchaseData->supplier_nickname }}） @endif （{{ $purchaseData->supplier_id }}）
                        </div>
                    @else
                        <select id="supplier" aria-label="採購廠商" required
                                class="form-select -select2 -single @error('supplier') is-invalid @enderror">
                            <option value="" selected disabled>請選擇</option>
                            @foreach ($supplierList as $supplierItem)
                                <option value="{{ $supplierItem->id }}"
                                        @if ($supplierItem->id == old('supplier')) selected @endif>
                                    {{ $supplierItem->name }}@if ($supplierItem->nickname)（{{ $supplierItem->nickname }}）（{{ $supplierItem->id }}） @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">
                            @error('supplier')
                            {{ $message }}
                            @enderror
                        </div>
                    @endif
                    <input type="hidden" name="supplier" value="{{ $purchaseData->supplier_id ?? '' }}">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">廠商預計進貨日期 <span class="text-danger">*</span></label>
                    @if ($hasCreatedFinalPayment)
                        <input type="date" id="scheduled_date" name="scheduled_date"
                               value="{{ old('scheduled_date', $purchaseData->scheduled_date  ?? '') }}"
                               class="form-control" aria-label="廠商預計進貨日期"
                               readonly/>
                    @else
                        <div class="input-group has-validation">
                            <input type="date" id="scheduled_date" name="scheduled_date"
                                   value="{{ old('scheduled_date', $purchaseData->scheduled_date  ?? '') }}"
                                   class="form-control @error('scheduled_date') is-invalid @enderror" aria-label="廠商預計進貨日期"
                                   required/>
                            <button class="btn btn-outline-secondary icon" type="button" data-clear
                                    data-bs-toggle="tooltip" title="清空日期"><i class="bi bi-calendar-x"></i>
                            </button>
                            <div class="invalid-feedback">
                                @error('scheduled_date')
                                {{ $message }}
                                @enderror
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">選擇預計入庫倉庫 <span class="text-danger">*</span></label>
                    @if ($method === 'edit' && ($hasCreatedFinalPayment || $audit_approved))
                        <input type="hidden" id="estimated_depot_id" name="estimated_depot_id"
                               value="{{ old('estimated_depot_id', $purchaseData->estimated_depot_id  ?? '') }}"
                               class="form-control" aria-label="預計入庫倉庫"
                               readonly/>
                        <input type="text" value="{{ $purchaseData->estimated_depot_name }}"
                               class="form-control"
                               readonly disabled/>
                    @else
                        <select name="estimated_depot_id"
                                class="form-select @error('estimated_depot_id') is-invalid @enderror"
                                aria-label="請選擇預計入庫倉庫" required>
                            <option value="" selected disabled>請選擇</option>
                            @foreach ($depotList as $depotItem)
                                <option value="{{ $depotItem['id'] }}"
                                        @if ($depotItem['id'] == old('estimated_depot_id', $purchaseData->estimated_depot_id  ?? '')) selected @endif>
                                    {{ $depotItem['name'] }} {{ $depotItem['can_tally'] ? '(理貨倉)' : '(非理貨倉)' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">
                            @error('estimated_depot_id')
                            {{ $message }}
                            @enderror
                        </div>
                    @endif
                </div>

                @if ($method === 'edit')
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">廠商訂單號</label>
                        <input class="form-control" name="supplier_sn" type="text" aria-label="廠商訂單號"
                               value="{{ old('supplier_sn', $purchaseData->supplier_sn  ?? '') }}"
                               @if ($hasCreatedFinalPayment) readonly @endif placeholder="請輸入廠商訂單號">
                    </div>
                @endif
            </div>
        </div>

        <div class="card shadow p-4 mb-4">
            <h6>採購清單</h6>
            <div class="table-responsive tableOverBox">
                <table class="table table-hover table-sm tableList mb-0">
                    <thead class="small">
                        <tr>
                            <th scope="col" class="text-center">刪除</th>
                            <th scope="col">商品名稱</th>
                            <th scope="col" class="lh-1">參考<br class="d-block d-xl-none">成本<br class="d-block d-xl-none">單價</th>
                            <th scope="col">採購數量</th>
                            <th scope="col">採購總價</th>
                            @if ($method === 'edit')
                                <th scope="col">狀態</th>
                                <th scope="col">入庫人員</th>
                            @endif
                            <th scope="col">採購備註</th>
                        </tr>
                    </thead>
                    <tbody class="-appendClone --selectedP">
                    @if (0 >= count(old('item_id', $purchaseItemData?? [])))
                        <tr class="-cloneElem --selectedP d-none">
                            <th class="text-center">
                                <button type="button"
                                        class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <input type="hidden" name="item_id[]" value="">
                                <input type="hidden" name="product_style_id[]" value="">
                                <input type="hidden" name="name[]" value="">
                                <input type="hidden" name="sku[]" value="">
                            </th>
                            <td class="wrap">
                                <div data-td="sku" class="lh-1 small text-nowrap text-secondary"></div>
                                <div data-td="name" class="lh-base"></div>
                            </td>
                            <td data-td="estimated_cost"></td>
                            <td>
                                <input type="number" class="form-control form-control-sm" name="num[]" min="1" value="" required/>
                            </td>
                            <td>
                                <div class="input-group input-group-sm flex-nowrap">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="number" class="form-control form-control-sm" name="price[]" min="0" step="1" value="" required/>
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm -xl" name="memo[]">
                            </td>
                        </tr>
                    @elseif(0 < count(old('item_id', $purchaseItemData?? [])))
                        @foreach (old('item_id', $purchaseItemData ?? []) as $psItemKey => $psItemVal)
                            <tr class="-cloneElem --selectedP">
                                <th class="text-center">
                                    <button type="button"
                                            class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <input type="hidden" name="item_id[]" value="{{ old('item_id.'. $psItemKey, $psItemVal->items_id?? '') }}">
                                    <input type="hidden" name="product_style_id[]" value="{{ old('product_style_id.'. $psItemKey, $psItemVal->product_style_id?? '') }}">
                                    <input type="hidden" name="name[]" value="{{ old('name.'. $psItemKey, $psItemVal->title?? '') }}">
                                    <input type="hidden" name="sku[]" value="{{ old('sku.'. $psItemKey, $psItemVal->sku?? '') }}">
                                    <input type="hidden" name="estimated_cost[]" value="{{ old('estimated_cost.'. $psItemKey, $psItemVal->estimated_cost?? '') }}">

                                    @if(true == $hasCreatedFinalPayment)
                                        <input type="hidden" name="num[]" value="{{ old('num.'. $psItemKey, $psItemVal->num?? '') }}">
                                        <input type="hidden" name="price[]" value="{{ old('price.'. $psItemKey, $psItemVal->price?? '') }}">
                                    @endif
                                </th>
                                <td class="wrap">
                                    <div data-td="sku" class="lh-1 small text-nowrap text-secondary">
                                        {{ old('sku.'. $psItemKey, $psItemVal->sku?? '') }}
                                    </div>
                                    <div data-td="name" class="lh-base">
                                        {{ old('name.'. $psItemKey, $psItemVal->title?? '') }}
                                    </div>
                                </td>
                                <td data-td="estimated_cost">{{ old('estimated_cost.'. $psItemKey, $psItemVal->estimated_cost?? '') }}</td>
                                <td>
                                    <input type="number" class="form-control form-control-sm @error('num.' . $psItemKey) is-invalid @enderror"
                                           name="num[]" value="{{ old('num.'. $psItemKey, $psItemVal->num?? '') }}" min="1" step="1" required/>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" class="form-control form-control-sm @error('price.' . $psItemKey) is-invalid @enderror"
                                               name="price[]" value="{{ old('price.'. $psItemKey, $psItemVal->price?? '') }}" min="0" step="0.01" required/>
                                    </div>
                                </td>
                                @if ($method === 'edit')
                                    <td data-td="inbound_status">{{$psItemVal->inbound_status?? ''}}</td>
                                    <td data-td="inbound_user_names">{{$psItemVal->inbound_user_names?? ''}}</td>
                                @endif
                                <td>
                                    <input type="text" class="form-control form-control-sm -xl" name="memo[]"
                                           value="{{ old('memo.'. $psItemKey, $psItemVal->memo?? '') }}"
                                           @if($hasReceivedFinalPayment) readonly @endif
                                    />
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                    <tfoot>
                    <tr>
                        <th class="lh-1"></th>
                        <th class="lh-1"></th>
                        <th class="lh-1"></th>
                        <th class="lh-1">價錢小計</th>
                        <th class="lh-1 text-end -sum">$ 0</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
            <div class="d-grid mt-3">
                @error('sku_repeat')
                <div class="alert alert-danger mt-3">{{ $message }}</div>
                @enderror
                @error('item_error')
                <div class="alert alert-danger mt-3">{{ $message }}</div>
                @enderror
                @if(false == ($hasCreatedFinalPayment?? false))
                    <button id="addProductBtn" type="button"
                            class="btn btn-outline-primary border-dashed" style="font-weight: 500;">
                        <i class="bi bi-plus-circle bold"></i> 加入商品
                    </button>
                @endif
            </div>
        </div>

        @if ($method === 'edit')
            <div id="logistics" class="card shadow p-4 mb-4">
                <h6>物流</h6>
                <div class="row mb-3" @if (!$hasLogistics) hidden @endif >
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">物流費用 <span class="text-danger">*</span></label>
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                            <input class="form-control" name="logistics_price" type="number" min="0" placeholder="請輸入運費"
                                   value="{{ old('logistics_price', $purchaseData->logistics_price  ?? '') }}"
                                   @if ($hasCreatedFinalPayment) readonly @endif
                                   @if ($hasLogistics) required @endif/>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">物流備註</label>
                        <input class="form-control" name="logistics_memo" type="text" placeholder="請輸入物流備註" aria-label="物流備註"
                               value="{{ old('logistics_memo', $purchaseData->logistics_memo  ?? '') }}"
                               @if ($hasCreatedFinalPayment) readonly @endif>
                    </div>
                </div>
                <div class="col-auto">
                    @if(false == $audit_approved)
                        <button class="btn btn-primary -add" type="button" role="button"
                                @if ($hasLogistics) hidden @endif>
                            <i class="bi bi-plus-lg"></i> 新增物流 {{$audit_approved}}
                        </button>
                        <button class="btn btn-outline-danger -del" type="button"
                                @if (!$hasLogistics) hidden @endif>
                            <i class="bi bi-trash"></i> 刪除物流
                        </button>
                        <mark class="fw-light small">
                            <i class="bi bi-exclamation-diamond-fill mx-2 text-warning"></i>有修改需要<b>儲存</b>才會生效呦！
                        </mark>
                    @else
                        @if (!$hasLogistics)
                            <label class="text-secondary">無</label>
                        @endif
                    @endif
                </div>
            </div>

            @if($audit_approved)
                <div class="card shadow p-4 mb-4">
                    <h6>付款資訊</h6>
                    <div class="row">
                        <div class="col-12 col-sm-6 mb-3">
                            <label class="form-label">訂金付款單@if($hasCreatedDepositPayment && $hasReceivedDepositPayment)<span class="text-danger">（已付完訂金）</span>@endif</label>
                            <div class="form-control" readonly>
                                @if($hasCreatedDepositPayment)
                                    <a href="{{ Route('cms.purchase.view-pay-order', ['id' => $id, 'type' => '0'], true) }}">{{ $depositPayData->sn }}</a>
                                @elseif($hasCreatedFinalPayment)
                                    已先建立尾款（無訂金付款單）
                                @elseif($hasReceivedFinalPayment)
                                    已付完尾款（無訂金付款單）
                                @else
                                    <a href="{{ Route('cms.purchase.pay-deposit', ['id' => $id], true) }}">新增訂金付款單</a>
                                @endif
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 mb-3 ">
                            <label class="form-label">尾款付款單@if($hasCreatedFinalPayment && $hasReceivedFinalPayment)<span class="text-danger">（已付完尾款）</span>@endif</label>
                            <div class="form-control" readonly>
                                @if($hasCreatedFinalPayment)
                                    <a href="{{ Route('cms.purchase.view-pay-order', ['id' => $id, 'type' => '1'], true) }}">{{ $finalPayData->sn }}</a>
                                @else
                                    @if($hasCreatedDepositPayment && !$hasReceivedDepositPayment)
                                        {{-- 尚未收到訂金 --}}
                                        訂金尚未補齊
                                    @else
                                        <a href="javascript:void(0)" id="finalPayment">新增尾款付款單</a>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 mb-3">
                            <label class="form-label">發票號碼</label>
                            <input class="form-control" name="invoice_num" type="text" placeholder="請輸入發票號碼" maxlength="80"
                                aria-label="發票號碼" value="{{ old('invoice_num', $purchaseData->invoice_num  ?? '') }}">
                        </div>
                        <div class="col-12 col-sm-6 mb-3">
                            <label class="form-label">發票日期</label>
                            <div class="input-group has-validation">
                                <input type="date" id="invoice_date" name="invoice_date"
                                       value="{{ old('invoice_date', $purchaseData->invoice_date  ?? '') }}"
                                       class="form-control @error('invoice_date') is-invalid @enderror" aria-label="發票日期"/>
                                <button class="btn btn-outline-secondary icon" type="button" data-clear
                                        data-bs-toggle="tooltip" title="清空日期"><i class="bi bi-calendar-x"></i>
                                </button>
                                <div class="invalid-feedback">
                                    @error('invoice_date')
                                    {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        @if(null != $purchaseData)
        <fieldset class="card shadow p-4 col-12 mb-3">
            <legend class="col-form-label p-0 mb-2">審核狀態 <span class="text-danger">*</span></legend>
            <div class="px-1 pt-1">
                @foreach (App\Enums\Consignment\AuditStatus::asArray() as $key => $val)
                    <div class="form-check form-check-inline @error('audit_status')is-invalid @enderror">
                        <label class="form-check-label">
                            <input class="form-check-input @error('audit_status')is-invalid @enderror" name="audit_status"
                                   value="{{ $val }}" type="radio" required
                                   @if (old('audit_status', $purchaseData->audit_status ?? '') == $val) checked @endif>
                            {{ App\Enums\Consignment\AuditStatus::getDescription($val) }}
                        </label>
                    </div>
                @endforeach
                @error('audit_status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @if($purchaseData != null)
                <div class="col">
                    <mark class="fw-light small">
                        <i class="bi bi-exclamation-diamond-fill mx-2 text-warning">
                        </i>審核狀態改為<b> 核可 或 否決 </b>就不能再修改預計進貨日期、採購清單和物流呦！
                    </mark>
                </div>
                @endif
            </div>
        </fieldset>
        @endif

        @error('del_error')
        <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror

        <div id="submitDiv">
            <div class="col-auto">
                <input type="hidden" name="del_item_id">
                <div class="col">
                    @if(null == $purchaseData)
                        <button type="submit" class="btn btn-primary px-4">儲存</button>
                    @elseif(!$hasCreatedFinalPayment && $purchaseData->close_date == null
                        && $purchaseData->audit_status == App\Enums\Consignment\AuditStatus::unreviewed()->value)
                        <button type="submit" class="btn btn-primary px-4">儲存</button>
                    @elseif($purchaseData->audit_status == App\Enums\Consignment\AuditStatus::approved()->value)
                        @if($hasReceivedFinalPayment)
                            <button type="submit" class="btn btn-primary px-4">儲存發票</button>
                        @else
                            <button type="submit" class="btn btn-primary px-4">儲存發票/採購備註</button>
                        @endif
                    @endif
                    <a href="{{ Route('cms.purchase.index', [], true) }}" class="btn btn-outline-primary px-4"
                       role="button">返回列表</a>
                </div>
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
                        <th scope="col">參考成本單價</th>
                        <th scope="col">官網可售數量</th>
                        <th scope="col">預扣庫存量</th>
                    </tr>
                    </thead>
                    <tbody class="-appendClone --product">
                    <tr>
                        <th class="text-center">
                            <input class="form-check-input" type="checkbox"
                                   value="" data-td="p_id" aria-label="選取商品">
                        </th>
                        <td data-td="name"></td>
                        <td data-td="spec"></td>
                        <td data-td="sku"></td>
                        <td data-td="estimated_cost">0</td>
                        <td></td>
                        <td></td>
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
            let supplierList = @json($supplierList);
            let hasCreatedFinalPayment = @json($hasCreatedFinalPayment?? false);

            if (true == hasCreatedFinalPayment) {
                $('.-cloneElem.--selectedP :input:not([name="memo[]"]):not(:hidden)').prop("disabled", true);
            }

            // 物流
            // -新增
            $('#logistics button.-add').off('click').on('click', function () {
                $('#logistics div.row, #logistics button.-del').prop('hidden', false);
                $('#logistics input[name="logistics_price"]').prop('required', true);
                $(this).prop('hidden', true);
            });
            // -刪除
            $('#logistics button.-del').off('click').on('click', function () {
                $('#logistics div.row, #logistics button.-del').prop('hidden', true);
                $('#logistics input[name="logistics_price"]').prop('required', false);
                $('#logistics input[name^="logistics_"]').val('');
                $('#logistics button.-add').prop('hidden', false);
            });

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
                $('input:hidden[name="supplier"]').val($('#supplier').val());
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

            $('#finalPayment').click(function (e) {
                $('#form1').attr('action', `{!! Route('cms.purchase.pay-order', ['id' => $id ?? '0', 'type' =>'1']) !!}`)
                    .submit(function (e) {
                        // 儲存前設定name
                        if ($('#supplier').length) {
                            $('input:hidden[name="supplier"]').val($('#supplier').val());
                        }
                    });
                $('#form1').submit();
            });

            // 儲存前設定name
            $('#form1').submit(function (e) {
                if ($('#supplier').length) {
                    $('input:hidden[name="supplier"]').val($('#supplier').val());
                }
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
                        $('#supplier').prop('disabled', true);
                        $('button[type="submit"]').prop('disabled', false);
                    } else if (@json($method) === 'create') {
                        $('#supplier').prop('disabled', false);
                    }
                    // 無商品不可儲存
                    if (!$('.-cloneElem.--selectedP').length) {
                        $('button[type="submit"]').prop('disabled', true);
                    }
                    sumPrice();
                }
            };
            Clone_bindDelElem($('.-cloneElem.--selectedP .-del'), delItemOption);
            // init bind
            bindPriceSum();
            sumPrice();

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
                    supplier_id: $('input:hidden[name="supplier"]').val(),
                    type: 'p'
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
                            <td data-td="estimated_cost">${p.estimated_cost}</td>
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
                                estimated_cost: $(element).parent('th').siblings('[data-td="estimated_cost"]').text(),
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
                bindPriceSum();

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
                            cloneElem.find('div[data-td="name"]').text(`${p.name}-${p.spec}`);
                            cloneElem.find('div[data-td="sku"]').text(p.sku);
                            cloneElem.find('td[data-td="estimated_cost"]').text(p.estimated_cost);
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

            // 綁定計算
            function bindPriceSum() {
                $('.-cloneElem.--selectedP input[name="num[]"]')
                    .off('change.sum').on('change.sum', function () {
                    sumEstimatedPrice();
                    sumPrice();
                });
                $('.-cloneElem.--selectedP input[name="price[]"]')
                    .off('change.sum').on('change.sum', function () {
                    sumPrice();
                });
            }

            /*
             商品總價 = 預估價格 * 數量
             */
            function sumEstimatedPrice() {
                let priceArray = [];
                let quantityArray = [];
                $('.-cloneElem.--selectedP td[data-td="estimated_cost"]').each(function () {
                    priceArray.push(Math.round(parseInt($(this).text())));
                });

                $('.-cloneElem.--selectedP input[name="num[]"]').each(function () {
                    quantityArray.push($(this).val());
                });
                $('.-cloneElem.--selectedP input[name="price[]"]').each(function (index) {
                    if (!isNaN(quantityArray[index])) {
                        //尚未有商品總價才更新
                        if ($(this).val() === "" ||
                            parseInt($(this).val()) === 0
                        ) {
                            $(this).val(priceArray[index] * quantityArray[index]);
                        }
                    }
                });
            }

            // 計算小計
            function sumPrice() {
                let sum = 0;
                $('.-cloneElem.--selectedP input[name="price[]"]').each(function (index, element) {
                    // element == this
                    const val = Number($(this).val());
                    sum = Number((sum + val).toFixed(2));
                });
                $('tfoot th.-sum').text(`$ ${formatNumber(sum.toFixed(2))}`);
            }
        </script>
    @endpush
@endonce

