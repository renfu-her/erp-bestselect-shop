@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">個人獎金</h2>
    
    <ul class="nav nav-tabs border-bottom-0">
        <li class="nav-item">
            <button class="nav-link -page1 active" aria-current="page" type="button">獎金資訊</button>
        </li>
        <li class="nav-item">
            <button class="nav-link -page2" type="button">修改紀錄</button>
        </li>
    </ul>
    
    <div id="page1">
        <div class="card shadow p-4 mb-4">
            @foreach ($subOrders as $subOrder)
            <div class="define-table table-warning text-nowrap mt-2">
                <dl class="d-flex mb-0">
                    <dt class="border">子訂單編號</dt>
                    <dd class="border border-start-0">{{ $subOrder->sn }}</dd>
                </dl>
            </div>
            <div class="table-responsive tableOverBox mb-3">
                <table class="table tableList table-striped mb-1">
                    <thead>
                        <tr>
                            <th scope="col" style="width:40px">#</th>
                            <th scope="col">品名規格</th>
                            <th scope="col" class="text-center px-3">金額</th>
                            <th scope="col" class="text-center px-3">數量</th>
                            <th scope="col" class="text-center px-3">小計</th>
                            <th scope="col" class="text-center px-3">當代獎金</th>
                            <th scope="col" class="text-center px-3">出庫數量</th>
                            <th scope="col">倉庫</th>
                            <th scope="col">產品人員</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subOrder->items as $item)
                            <tr>
                                <th scope="row">1</th>
                                <td>{{ $item->product_title }}</td>
                                <td class="text-center">$ {{ number_format($item->price) }}</td>
                                <td class="text-center">{{ number_format($item->qty) }}</td>
                                <td class="text-center">$ {{ number_format($item->total_price) }}</td>
                                <td class="text-center">$ {{ number_format(0) }}</td>
                                <td class="text-center">{{ number_format(0) }}</td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach
        </div>
    </div>
    
    <div id="page2" hidden>
        <div class="card shadow p-4 mb-4">
            <div class="table-responsive tableOverBox">
                <table class="table tableList table-hover table-striped">
                    <thead>
                        <tr>
                            <th scope="col">更改人員</th>
                            <th scope="col">修改時間</th>
                            <th scope="col">品名規格</th>
                            <th scope="col" class="text-end">當代獎金</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>-</td>
                            <td>{{ date('Y/m/d H:i:s', strtotime('2022/7/4 15:45:55')) }}</td>
                            <td>-</td>
                            <td class="text-end">$ {{ number_format(0) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>
        <a href="{{ Route('cms.order.detail', ['id' => $id]) }}" 
            class="btn btn-outline-primary px-4" role="button">返回明細</a>
    </div>
@endsection

@once
    @push('sub-scripts')
    <script>
        $('.nav-link').off('click').on('click', function () {
            const $this = $(this);
            const page = $this.hasClass('-page1') ? 'page1' : $this.hasClass('-page2') ? 'page2' : '';

            // tab
            $('.nav-link').removeClass('active').removeAttr('aria-current');
            $this.addClass('active').attr('aria-current', 'page');
            // page
            $('#page1, #page2').prop('hidden', true);
            $(`#${page}`).prop('hidden', false);
        });
    </script>
    @endpush
@endonce