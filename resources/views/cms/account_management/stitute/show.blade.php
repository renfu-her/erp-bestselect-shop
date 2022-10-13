@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">代墊單</h2>
    
    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            @can('cms.stitute.edit')
            @if(! $stitute_order->po_id)
            <a href="{{ route('cms.stitute.edit', ['id' => $stitute_order->so_id]) }}" class="btn btn-sm btn-success px-3" role="button">修改</a>
            @endif
            @endcan

            @if(! $stitute_order->po_payment_date)
                <a href="{{ route('cms.stitute.po-edit', ['id' => $stitute_order->so_id]) }}" 
                    class="btn btn-sm btn-primary px-3" role="button">付款</a>
            @endif
            {{--
            <button type="submit" class="btn btn-danger">A4列印畫面</button>
            --}}
            @can('cms.stitute.delete')
            @if(! $stitute_order->po_id)
            <a href="javascript:void(0)" role="button" class="btn btn-outline-danger btn-sm"
                data-bs-toggle="modal" data-bs-target="#confirm-delete"
                data-href="{{ Route('cms.stitute.delete', ['id' => $stitute_order->so_id]) }}">刪除代墊單</a>
            @endif
            @endcan

            <a href="{{ url()->full() . '?action=print' }}" target="_blank" 
                class="btn btn-sm btn-warning" rel="noopener noreferrer">中一刀列印畫面</a>
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
            <h4 class="text-center">代　墊　單</h4>
            <hr>

            <dl class="row mb-0">
                <div class="col">
                    <dd>付款單號：{{ $stitute_order->so_sn }}</dd>
                </div>
                <div class="col">
                    <dd>製表日期：{{ date('Y-m-d', strtotime($stitute_order->so_created_at)) }}</dd>
                </div>
            </dl>
            <dl class="row mb-0">
                <div class="col">
                    <dd>單據編號：</dd>
                </div>
                <div class="col">
                    <dd>付款日期：{{ $stitute_order->so_payment_date ? date('Y-m-d', strtotime($stitute_order->so_payment_date)) : '' }}</dd>
                </div>
            </dl>
            <dl class="row mb-0">
                <div class="col">
                    <dd>支付對象：{{ $stitute_order->so_client_name }}</dd>
                </div>
                <div class="col">
                    <dd>承辦人：{{ $stitute_order->creator_name }}</dd>
                </div>
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
                        @if($stitute_order->so_items)
                        @foreach(json_decode($stitute_order->so_items) as $data)
                        <tr>
                            <td>{{ $data->grade_code . ' ' . $data->grade_name . ' ' . $data->summary }}</td>
                            <td class="text-end">{{ $data->qty }}</td>
                            <td class="text-end">{{ number_format($data->price, 2) }}</td>
                            <td class="text-end">{{ number_format($data->total_price) }}</td>
                            <td>@php echo $data->memo ?? '' @endphp</td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3">
                                <div class="d-flex justify-content-between">
                                    <span>合計：</span>
                                    <span>（{{ $zh_price }}）</span>
                                </div>
                            </td>
                            <td class="text-end">{{ number_format($stitute_order->so_price) }}</td>
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
                    <dd>會計：{{ $stitute_order->accountant_name }}</dd>
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
        @can('cms.stitute.index')
        <a href="{{ session('stitute_url') ?? route('cms.stitute.index') }}" class="btn btn-outline-primary px-4" role="button">
            返回 代墊單作業
        </a>
        @endcan
    </div>

    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">確認要刪除此代墊單？</x-slot>
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