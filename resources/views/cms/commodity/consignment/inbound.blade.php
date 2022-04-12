@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">寄倉單 {{ $purchaseData->consignment_sn }}</h2>
    <x-b-pch-navi :id="$id"></x-b-pch-navi>

    <div class="card shadow p-4 mb-4">
        <h6>採購單入庫總覽</h6>
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-1">
                <thead>
                <tr>
                    <th scope="col">商品</th>
                    <th scope="col">款式</th>
                    <th scope="col">SKU</th>
                    <th scope="col">採購數量</th>
                    <th scope="col">已入庫數量</th>
                    <th scope="col">異常數量</th>
                    <th scope="col">狀態</th>
                    <th scope="col">商品負責人</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($inboundOverviewList as $overview)
                    <tr>
                        <td>{{ $overview->product_title }}</td>
                        <td>{{ $overview->style_title }}</td>
                        <td>{{ $overview->sku }}</td>
                        <td>{{ $overview->num }}</td>
                        <td>{{ $overview->inbound_num }}</td>
                        <td>{{ $overview->should_enter_num }}</td>
                        <td @class(['text-danger' => $overview->inbound_type === '短缺' || $overview->inbound_type === '溢出'])>{{ $overview->inbound_type }}</td>
                        <td>{{ $overview->user_name }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @error('close_error')
        <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror
    </div>

    @if(null != $purchaseData->audit_date)
        <div class="card shadow p-4 mb-4">
            <form id="form1" method="post" action="{{ $formAction }}">
                @method('POST')
                @csrf

                @error('id')
                <div class="alert alert-danger mt-3">{{ $message }}</div>
                @enderror

                <h6>本次入庫資料</h6>
                <div class="row mb-4">
                    <div class="col-12">

                        <label class="form-label">入庫倉庫
                        <div>
                        @foreach ($depotList as $depotItem)
                            @if($depotItem['id'] == $purchaseData->receive_depot_id)
                                <input type="hidden"
                                       class="form-control form-control-sm @error('depot_id') is-invalid @enderror"
                                       name="depot_id" value="{{ $depotItem['id'] }}"
                                       required readonly/>{{ $depotItem['name'] }} {{ $depotItem['can_tally'] ? '(理貨倉)' : '(非理貨倉)' }}
                            @endif
                        @endforeach
                        </div>
                        <div class="invalid-feedback">
                            @error('depot_id')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <label class="form-label">入庫資訊</label>
                <div class="table-responsive tableOverBox">
                    <table class="table table-hover tableList mb-1">
                        <thead>
                        <tr>
                            <th scope="col" class="text-center">刪除</th>
                            <th scope="col">入庫日期</th>
                            <th scope="col">商品款式</th>
                            <th scope="col">SKU</th>
                            <th scope="col">應進數量</th>
                            <th scope="col">實進數量</th>
                            <th scope="col">有效期限</th>
                            <th scope="col">備註</th>
                        </tr>
                        </thead>
                        <tbody class="-appendClone">
                        @foreach (old('product_style_id', $purchaseItemList ?? []) as $styleKey => $styleVal)
                            <tr class="-cloneElem">
                                <th class="text-center">
                                    <button type="button"
                                            class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <input type="hidden" name="event_item_id[]"
                                           value="{{ old('event_item_id.'. $styleKey, $styleVal->rcv_deppot_id?? '') }}">
                                    <input type="hidden" name="product_style_id[]"
                                           value="{{ old('product_style_id.'. $styleKey, $styleVal->product_style_id?? '') }}">
                                    <input type="hidden" name="origin_inbound_id[]"
                                           value="{{ old('product_style_id.'. $styleKey, $styleVal->inbound_id?? '') }}">
                                </th>
                                <td>
                                    <input type="date"
                                           class="form-control form-control-sm @error('inbound_date.' . $styleKey) is-invalid @enderror"
                                           name="inbound_date[]"
                                           value="{{ old('inbound_date.'. $styleKey, date('Y-m-d')) }}" required/>
                                </td>
                                <td data-td="title">{{ old('title.'. $styleKey, $styleVal->product_title?? '') }}</td>
                                <td data-td="sku">{{ old('sku.'. $styleKey, $styleVal->sku?? '') }}</td>
                                <td data-td="should_enter_num">{{ old('should_enter_num.'. $styleKey, $styleVal->should_enter_num?? '') }}</td>
                                <td>
                                    <input type="number"
                                           class="form-control form-control-sm @error('inbound_num.' . $styleKey) is-invalid @enderror"
                                           name="inbound_num[]" value="{{ old('inbound_num.'. $styleKey, '') }}" min="1"
                                           required/>
                                </td>
                                <td>
                                    <input type="date"
                                           class="form-control form-control-sm @error('expiry_date.' . $styleKey) is-invalid @enderror"
                                           name="expiry_date[]" value="{{ old('sku.'. $styleKey, $styleVal->expiry_date?? '') }}"
                                           required readonly/>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" name="inbound_memo[]"
                                           value="{{ old('inbound_memo.'. $styleKey, '') }}"/>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-grid mt-3">
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary px-4">確認入庫</button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    <div class="card shadow p-4 mb-4">
        <h6>歷史入庫資料</h6>
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-1">
                <thead>
                <tr>
                    @if(null == $purchaseData->audit_date)
                        <th scope="col" class="text-center">取消入庫</th>
                    @endif
                    <th scope="col">入庫單號</th>
                    <th scope="col">入庫日期</th>
                    <th scope="col">商品名稱</th>
                    <th scope="col">款式名稱</th>
                    <th scope="col">SKU</th>
                    <th scope="col">實進數量</th>
                    <th scope="col">有效期限</th>
                    <th scope="col">備註</th>
                    <th scope="col">倉庫</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($inboundList as $inbound)
                    <tr>
                        @if(null == $purchaseData->audit_date)
                            <th class="text-center">
                                @if(null == $inbound->deleted_at)
                                <button type="button"
                                        data-href="{{ Route('cms.consignment.delete_inbound', ['id' => $inbound->inbound_id], true) }}"
                                        data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                        class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </th>
                        @endif
                        <td>{{ $inbound->inbound_sn }}</td>
                        <td>{{ $inbound->inbound_date }}</td>
                        <td>{{ $inbound->product_title }}</td>
                        <td>{{ $inbound->style_title }}</td>
                        <td>{{ $inbound->style_sku }}</td>
                        <td>{{ $inbound->inbound_num }}</td>
                        <td>{{ $inbound->expiry_date }}</td>
                        @if(null != $inbound->deleted_at)
                            <td>取消入庫</td>
                        @else
                            <td>{{ $inbound->inbound_memo }}</td>
                        @endif
                        <td>{{ $inbound->depot_name }}</td>

                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <div class="col-auto">
            @if(null == $purchaseData->close_date)
            <button type="button"
                    data-bs-toggle="modal" data-bs-target="#confirm-close"
                    class="btn btn-primary px-4">
                結案
            </button>
            @endif
            <a href="{{ Route('cms.consignment.edit', ['id' => $id], true) }}" class="btn btn-outline-primary px-4"
                role="button">返回列表</a>
        </div>
    </div>

    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">刪除後將無法復原！確認要刪除？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
        </x-slot>
    </x-b-modal>
    <x-b-modal id="confirm-close">
        <x-slot name="title">結案確認</x-slot>
        <x-slot name="body">結案後將無法編輯入庫！確認要結案？</x-slot>
        <x-slot name="foot">
            <form method="post" action="{{ $formActionClose }}">
                @method('POST')
                @csrf
                <button type="submit" class="btn btn-primary btn-ok">確認並結案</button>
            </form>
        </x-slot>
    </x-b-modal>
@endsection
@once
    @push('sub-scripts')
        <script>
            // Modal del
            $('#confirm-delete').on('show.bs.modal', function (e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
            Clone_bindDelElem($('.-cloneElem .-del'));
        </script>
    @endpush
@endonce
