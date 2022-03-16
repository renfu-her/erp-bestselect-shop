@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $sn }} 出貨審核</h2>
    <form method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>商品列表 {{$delivery->close_date}}</h6>
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
                            <td data-td="o_qty">{{ number_format($ord->qty) }}</td>
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
                                                <button href="javascript:void(0)" type="button"
                                                    data-bid="{{ $rec->inbound_id }}" data-rid="{{ $rec->id }}"
                                                    data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                                    @if (isset($delivery->close_date)) disabled @endif
                                                    class="icon icon-btn -del fs-5 text-danger rounded-circle border-0">
                                                    <i class="bi bi-trash"></i>
                                                </button>
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
                <button type="submit" class="btn btn-primary px-4" @if (isset($delivery->close_date)) disabled @endif>送出審核</button>
                @if($delivery->event == App\Enums\Delivery\Event::order()->value)
                    <a href="{{ Route('cms.order.detail', ['id' => $order_id, 'subOrderId' => $delivery->id ]) }}" class="btn btn-outline-primary px-4" role="button">前往出貨單明細</a>
                @endif
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
    @push('sub-scripts')
        <script src="{{ Asset('dist/js/deliveryAudit.js') }}"></script>
        <script>
        $(function () {
            const CreateUrl = @json(Route('api.cms.delivery.create-receive-depot'));
            const DelUrl = "{{ Route('cms.delivery.delete', ['event'=>$event, 'eventId'=>$sub_order_id, 'receiveDepotId'=>'#'], true)}}".replace('#', '');
            const DeliveryId = @json($delivery_id);
            const Readonly = @json(isset($delivery->close_date));

            // init
            DvySumExportQty();
            DvyCheckSubmit(Readonly);

            // 刪除
            $('#confirm-delete').on('show.bs.modal', function (e) {
                console.log($(e.relatedTarget).data('rid'));
                $(this).find('.btn-ok').attr('href', DelUrl + $(e.relatedTarget).data('rid'));
            });

            // btn - 加入入庫單
            $('#addInbound .btn-ok').off('click').on('click', function () {
                const $okBtn = $(this);
                if (!DvyCheckSelectQty()) {
                    alert('預計出貨數量不合，請檢查！');
                    return false;
                }
                // call API
                DvyCreateReceiveDepot($okBtn, CreateUrl, DeliveryId);
            });
        });
        </script>
    @endpush
@endonce
