@extends('layouts.main')
@section('sub-content')
@if ($_SERVER['SERVER_NAME'] === '127.0.0.1' || $_SERVER['SERVER_NAME'] === 'localhost')
    <a href="/demo" class="btn btn-warning mb-3">Demo page</a>
@endif

<div class="d-flex flex-wrap">
    <div class="col-12 col-lg-9">
        {{-- 電商訂單 --}}
        <h6 class="px-2"><i class="bi bi-cart-fill me-2"></i>電商訂單</h6>
        <div class="d-flex flex-column flex-md-row mb-3 border bg-white inner-border">
            <div class="col px-3 py-2 d-flex align-items-end">
                <div>
                    NT$ <span class="data text-nowrap">{{ number_format(123456) }}</span> / 
                    <span class="text-nowrap">78筆訂單</span>
                </div>
                <div class="title bg-danger text-white">今天</div>
            </div>
            <div class="col px-3 py-2 d-flex align-items-end">
                <div>
                    NT$ <span class="data text-nowrap">{{ number_format(123456) }}</span> / 
                    <span class="text-nowrap">78筆訂單</span>
                </div>
                <div class="title bg-secondary text-white">本月</div>
            </div>
            <div class="col px-3 py-2 d-flex align-items-end">
                <div>
                    NT$ <span class="data text-nowrap">{{ number_format(123456) }}</span> / 
                    <span class="text-nowrap">78筆訂單</span>
                </div>
                <div class="title bg-secondary text-white">上月</div>
            </div>
        </div>

        {{-- 電商流量 --}}
        {{--
        <h6 class="px-2"><i class="bi bi-bar-chart-line-fill me-2"></i>電商流量</h6>
        <div class="d-flex flex-column flex-md-row mb-3 border bg-white inner-border">
            <div class="col px-3 py-2 d-flex align-items-end">
                <div><span class="data">{{ number_format(135790) }}</span> 來訪數</div>
                <div class="title bg-success text-white">昨日</div>
            </div>
            <div class="col px-3 py-2 d-flex align-items-end">
                <div><span class="data">{{ number_format(135790) }}</span> 來訪數</div>
                <div class="title bg-secondary text-white">本月</div>
            </div>
            <div class="col px-3 py-2 d-flex align-items-end">
                <div><span class="data">{{ number_format(135790) }}</span> 來訪數</div>
                <div class="title bg-secondary text-white">上月</div>
            </div>
        </div>
        --}}

        {{-- 推薦商品 --}}
        <h6 class="px-2"><i class="bi bi-hand-thumbs-up-fill me-2"></i>推薦商品</h6>
        <div id="Rec_group" class="row g-3 mb-3 text-center">
            <div class="col-6 col-xl">
                <a href="#" target="_blank" data-bs-toggle="tooltip" title="查看群組">
                    <div class="px-3 py-2 text-end d-flex align-items-center justify-content-end lh-sm">冷凍商品超值組合</div>
                </a>
            </div>
            <div class="col-6 col-xl">
                <a href="#" target="_blank" data-bs-toggle="tooltip" title="查看群組" >
                    <div class="px-3 py-2 text-end d-flex align-items-center justify-content-end lh-sm">冷凍商品超值組合</div>
                </a>
            </div>
            <div class="col-6 col-xl">
                <a href="#" target="_blank" data-bs-toggle="tooltip" title="查看群組" >
                    <div class="px-3 py-2 text-end d-flex align-items-center justify-content-end lh-sm">冷凍商品超值組合</div>
                </a>
            </div>
            <div class="col-6 col-xl">
                <a href="#" target="_blank" data-bs-toggle="tooltip" title="查看群組" >
                    <div class="px-3 py-2 text-end d-flex align-items-center justify-content-end lh-sm">冷凍商品超值組合</div>
                </a>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <h6 class="px-2"><i class="bi bi-bell-fill me-2"></i>通知事項</h6>
                <div class="border bg-body">
                    {{-- <h6 class="px-3 py-2 mb-0 border-bottom"><i class="bi bi-bell-fill"></i> 通知事項</h6> --}}
                    <table class="table table-striped mb-0">
                        <thead class="text-nowrap">
                            <tr>
                                <th style="width: 20%">日期</th>
                                <th>事項</th>
                                <th style="width: 10%">發佈者</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < 5; $i++)
                                <tr>
                                    <td>{{ date('Y/m/d H:i', strtotime('2022-08-01 15:18:55')) }}</td>
                                    <td>更新DEV</td>
                                    <td class="text-nowrap">Hans</td>
                                </tr>
                                <tr>
                                    <td>{{ date('Y/m/d H:i', strtotime('2022-08-01 15:13:55')) }}</td>
                                    <td>請假啦~</td>
                                    <td class="text-nowrap">烏梅</td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                    <nav class="mx-3 my-2">
                        <ul class="pagination pagination-sm justify-content-end">
                          <li class="page-item">
                                <a class="page-link" href="#" aria-label="Previous">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                          </li>
                          <li class="page-item"><a class="page-link" href="#">1</a></li>
                          <li class="page-item"><a class="page-link" href="#">2</a></li>
                          <li class="page-item"><a class="page-link" href="#">3</a></li>
                          <li class="page-item">
                                <a class="page-link" href="#" aria-label="Next">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                          </li>
                        </ul>
                    </nav>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <h6 class="px-2"><i class="bi bi-megaphone-fill me-2"></i>公告事項</h6>
                <div class="border bg-body">
                    {{-- <h6 class="px-3 py-2 mb-0 border-bottom"><i class="bi bi-megaphone-fill"></i> 公告事項</h6> --}}
                    <table class="table table-striped mb-0">
                        <thead class="text-nowrap">
                            <tr>
                                <th style="width: 20%">日期</th>
                                <th>公告事項</th>
                                <th style="width: 10%">公告者</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < 5; $i++)
                                <tr>
                                    <td>{{ date('Y/m/d H:i', strtotime('2022-08-01 15:18:55')) }}</td>
                                    <td>系統上線啦!</td>
                                    <td class="text-nowrap">理查</td>
                                </tr>
                                <tr>
                                    <td>{{ date('Y/m/d H:i', strtotime('2022-08-01 15:13:55')) }}</td>
                                    <td>拉肚子了</td>
                                    <td class="text-nowrap">之谷</td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                    <nav class="mx-3 my-2">
                        <ul class="pagination pagination-sm justify-content-end">
                          <li class="page-item">
                                <a class="page-link" href="#" aria-label="Previous">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                          </li>
                          <li class="page-item"><a class="page-link" href="#">1</a></li>
                          <li class="page-item"><a class="page-link" href="#">2</a></li>
                          <li class="page-item"><a class="page-link" href="#">3</a></li>
                          <li class="page-item">
                                <a class="page-link" href="#" aria-label="Next">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                          </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="col ms-0 ms-lg-3">
        <h6 class="px-2"><i class="bi bi-trophy-fill me-2"></i>排行榜</h6>
        <div class="bg-body">
            {{-- <h6 class="border border-bottom-0 px-3 py-2 mb-0"><i class="bi bi-trophy-fill"></i> 排行榜</h6> --}}
            <table class="table table-striped table-bordered table-sm mb-0">
                <thead class="text-nowrap text-center">
                    <tr>
                        <th style="width: 10%">名次</th>
                        <th style="width: 45%">本月</th>
                        <th style="width: 45%">上月</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 1; $i <= 20; $i++)
                        <tr>
                            <td class="text-center">{{ $i }}</td>
                            <td>理查 / {{ number_format(9999) }}</td>
                            <td>理查 / {{ number_format(9999) }}</td>
                        </tr>
                    @endfor
                </tbody>
                <caption class="text-end border-top-0">更新時間：{{ date('Y/m/d H:i', strtotime('2022-7-27 18:29:30')) }}</caption>
            </table>
        </div>
    </div>
</div>
@endsection
@once
    @push('sub-styles')
    <style>
        .inner-border > div:not(:last-child) {
            border-right: 1px solid var(--bs-gray-300);
        }
        @media (max-width: 768px) {
            .inner-border > div:not(:last-child) {
                border-bottom: 1px solid var(--bs-gray-300);
                border-right: none;
            }
        }
        .inner-border > div {
            position: relative;
            font-size: .8rem;
            height: 70px;
            color: var(--bs-gray-700);
        }
        .inner-border .title {
            position: absolute;
            top: .4rem;
            right: .4rem;
            padding: 2px 5px;
            font-size: 12px;
        }
        .inner-border .title.down {
            top: unset;
            bottom: .5rem;
        }
        .inner-border .data {
            font-size: 1.2rem;
            font-weight: bold;
        }
        table {
            font-size: .9rem;
        }
        #Rec_group > div div {
            color: #FFFFFF;
            background-position: left center;
            background-size: auto 100%;
            background-repeat: no-repeat;
            background-origin: border-box;
            min-height: 60px;
        }
        #Rec_group > div div:hover {
            box-shadow: 0 0.3rem 0.25rem rgb(0 0 0 / 20%);
        }
        #Rec_group > div:nth-child(1) div {
            background-color: #EBBA0B;
            background-image: url('/../images/bg_1.png');
        }
        #Rec_group > div:nth-child(2) div {
            background-color: #26A1D7;
            background-image: url('/../images/bg_2.png');
        }
        #Rec_group > div:nth-child(3) div {
            background-color: #9ABB1F;
            background-image: url('/../images/bg_3.png');
        }
        #Rec_group > div:nth-child(4) div {
            background-color: #58C4D1;
            background-image: url('/../images/bg_4.png');
        }
    </style>
    @endpush
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endOnce
