@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">代墊單</h2>
    
    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            <a href="{{ route('cms.stitute.edit', ['id' => $stitute_order->id]) }}" class="btn btn-sm btn-success px-3" role="button">修改</a>

            @if(! $stitute_order->payment_date)
                <a href="{{ route('cms.stitute.po-edit', ['id' => $stitute_order->id]) }}" 
                    class="btn btn-sm btn-primary px-3" role="button">付款</a>
            @else
                <a href="javascript:void(0)" role="button" class="btn btn-outline-danger btn-sm"
                    data-bs-toggle="modal" data-bs-target="#confirm-delete"
                    data-href="{{ Route('cms.collection_payment.delete', ['id' => $stitute_order->pay_order_id]) }}">刪除付款單</a>
            @endif
            {{--
            <button type="submit" class="btn btn-danger">中一刀列印畫面</button>
            <button type="submit" class="btn btn-danger">A4列印畫面</button>
            --}}
        </div>
    </nav>

    <div class="card shadow p-4 mb-4">
        <div class="mb-3">
            <h4 class="text-center">{{ $applied_company->company }}</h4>
            <div class="text-center small mb-2">
                <span>地址：{{ $applied_company->address }}</span>
                <span class="ms-3">電話：{{ $applied_company->phone }}</span>
                <span class="ms-3">傳真：{{ $applied_company->fax }}</span>
            </div>
            <h4 class="text-center">代墊單</h4>
            <hr>
            
            <dl class="row mb-0">
                <div class="col">
                    <dd>編號：{{ $stitute_order->sn }}</dd>
                </div>
                <div class="col">
                    <dd>日期：{{ date('Y-m-d', strtotime($stitute_order->created_at)) }}</dd>
                </div>
            </dl>

            <dl class="row mb-0">
                <div class="col">
                    <dd>支付對象：{{ $stitute_order->client_name }}</dd>
                </div>
                <div class="col">
                    <dd>承辦人：{{ $sales ? $sales->name : '' }}</dd>
                </div>
            </dl>

            <dl class="row mb-0">
                <div class="col">
                {{--
                    <dd>訂單流水號：<a href="{{ Route('cms.order.detail', ['id' => $order->id], true) }}">{{ $order->sn }}</a></dd>
                --}}
                </div>
                @if($stitute_order->payment_date)
                <div class="col">
                    <dd>付款日期：{{ date('Y-m-d', strtotime($stitute_order->payment_date)) }}</dd>
                </div>
                @endif
            </dl>
        </div>

        <div class="mb-2">
            <div class="table-responsive tableoverbox">
                <table class="table tablelist table-sm mb-0 align-middle">
                    <thead class="table-light text-secondary text-nowrap">
                        <tr>
                            <th scope="col">代墊項目</th>
                            <th scope="col" class="text-end">數量</th>
                            <th scope="col" class="text-end">單價</th>
                            <th scope="col" class="text-end">應付金額</th>
                            <th scope="col">備註</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $stitute_grade->code . ' ' . $stitute_grade->name . ' ' . $stitute_order->summary }}</td>
                            <td class="text-end">{{ $stitute_order->qty }}</td>
                            <td class="text-end">{{ number_format($stitute_order->price, 2) }}</td>
                            <td class="text-end">{{ number_format($stitute_order->total_price) }}</td>
                            <td>{{-- $stitute_order->taxation == 1 ? '應稅' : '免稅' --}}{{ $stitute_order->memo }}</td>
                        </tr>

                        
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3">
                                <div class="d-flex justify-content-between">
                                    <span>合計：</span>
                                    <span>（{{ $zh_price }}）</span>
                                </div>
                            </td>
                            <td class="text-end">{{ number_format($stitute_order->total_price) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div>
            <dl class="row">
                <div class="col">
                    <dd>財務主管：</dd>
                </div>
                <div class="col">
                    <dd>會計：{{ $accountant ? $accountant->name : '' }}</dd>
                </div>
                <div class="col">
                    <dd>商品主管：</dd>
                </div>
                <div class="col">
                    <dd>商品負責人：</dd>
                </div>
            </dl>
        </div>
    </div>
    
    <div class="col-auto">
        <a href="{{ Route('cms.stitute.index') }}" class="btn btn-outline-primary px-4" role="button">
            返回列表
        </a>
    </div>

    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">確認要刪除此付款單？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
        </x-slot>
    </x-b-modal>
@endsection

@once
    @push('sub-scripts')
    <script>
        // Modal Control
        $('#confirm-delete').on('show.bs.modal', function(e) {
            $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
        });
    </script>
    @endpush
@endonce