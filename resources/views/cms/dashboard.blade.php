@extends('layouts.main')
@section('sub-content')
@if ($_SERVER['SERVER_NAME'] === '127.0.0.1' || $_SERVER['SERVER_NAME'] === 'localhost')
    <a href="/demo" class="btn btn-warning mb-3">Demo page</a>
@endif

<div class="d-flex flex-wrap">
    <div class="col-12 col-lg-9">
        {{-- 電商訂單 --}}
        <div class="d-flex flex-column flex-md-row mb-3 border bg-white inner-border">
            <div class="col p-3 d-flex align-items-end">
                <div>NT$ <span class="data">123456</span> / 78筆訂單</div>
                <div class="title bg-danger text-white">今天</div>
            </div>
            <div class="col p-3 d-flex align-items-end">
                <div>NT$ <span class="data">123456</span> / 78筆訂單</div>
                <div class="title bg-secondary text-white">本月</div>
            </div>
            <div class="col p-3 d-flex align-items-end">
                <div>NT$ <span class="data">123456</span> / 78筆訂單</div>
                <div class="title bg-secondary text-white">上月</div>
            </div>
        </div>

        {{-- 電商流量 --}}
        <div class="d-flex flex-column flex-md-row mb-3 border bg-white inner-border">
            <div class="col p-3 d-flex align-items-end">
                <div><span class="data">135790</span> 來訪數</div>
                <div class="title bg-success text-white">昨日</div>
            </div>
            <div class="col p-3 d-flex align-items-end">
                <div><span class="data">135790</span> 來訪數</div>
                <div class="title bg-secondary text-white">本月</div>
            </div>
            <div class="col p-3 d-flex align-items-end">
                <div><span class="data">135790</span> 來訪數</div>
                <div class="title bg-secondary text-white">上月</div>
            </div>
        </div>

        {{-- 推薦商品 --}}
        <div class="row g-3 mb-3 text-center">
            <div class="col-6 col-md-3">
                <a href="#" target="_blank" data-bs-toggle="tooltip" title="查看群組" data-bs-placement="bottom">
                    <div class="p-3 border">群組1</div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="#" target="_blank" data-bs-toggle="tooltip" title="查看群組" data-bs-placement="bottom">
                    <div class="p-3 border">群組2</div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="#" target="_blank" data-bs-toggle="tooltip" title="查看群組" data-bs-placement="bottom">
                    <div class="p-3 border">群組3</div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="#" target="_blank" data-bs-toggle="tooltip" title="查看群組" data-bs-placement="bottom">
                    <div class="p-3 border">群組4</div>
                </a>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <div class="border bg-body">
                    <h6 class="px-3 py-2 mb-0 border-bottom"><i class="bi bi-bell-fill"></i> 通知事項</h6>
                    <table class="table table-striped mb-0">
                        <thead class="text-nowrap">
                            <tr>
                                <th style="width: 20%">日期</th>
                                <th>事項</th>
                                <th style="width: 10%">發佈者</th>
                            </tr>
                        </thead>
                        <tbody>
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
                <div class="border bg-body">
                    <h6 class="px-3 py-2 mb-0 border-bottom"><i class="bi bi-megaphone-fill"></i> 公告事項</h6>
                    <table class="table table-striped mb-0">
                        <thead class="text-nowrap">
                            <tr>
                                <th style="width: 20%">日期</th>
                                <th>公告事項</th>
                                <th style="width: 10%">公告者</th>
                            </tr>
                        </thead>
                        <tbody>
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
        <div class="bg-body">
            <h6 class="border border-bottom-0 px-3 py-2 mb-0"><i class="bi bi-trophy-fill"></i> 排行榜</h6>
            <table class="table table-striped table-bordered table-sm mb-0">
                <caption class="text-end">更新時間：{{ date('Y/m/d H:i', strtotime('2022-7-27 18:29:30')) }}</caption>
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
                            <td>理查 / 9999</td>
                            <td>理查 / 9999</td>
                        </tr>
                    @endfor
                </tbody>
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
            top: .5rem;
            right: .5rem;
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
    </style>
    @endpush
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endOnce
