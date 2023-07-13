@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">消費者紅利點數</h2>
    <form id="search" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6 class="mb-2">搜尋條件</h6>
            <div class="row">
                <label class="form-label">關鍵字</label>
                <div class="col">
                    <input class="form-control" type="text" name="keyword" placeholder="請輸入姓名或email" value=""
                        aria-label="關鍵字">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary px-4">搜尋</button>
                </div>
            </div>
        </div>
    </form>
    <div class="card shadow p-4 mb-4">
        <h6 class="mb-2">統計總覽</h6>
        <table class="table table-sm table-bordered text-center mb-0">
            <thead class="small align-middle">
                <td class="table-primary lh-sm">購物訂單<span class="d-inline-block">發放總數</span><br>
                    (購物訂單 + 喜鴻購物2.0<span class="d-inline-block">取得)</span>
                </td>
                <td class="table-primary lh-sm">旅遊企業<span class="d-inline-block">領取總數</span></td>
                <td class="table-primary lh-sm">旅遊會員<span class="d-inline-block">領取總數</span></td>
                <td class="table-primary lh-sm">旅遊同業<span class="d-inline-block">領取總數</span></td>
                <td class="table-danger lh-sm">已使用<span class="d-inline-block">總數</span></td>
            </thead>
            <tbody>
                <td>{{ number_format($total['normal_get']) }}</td>
                <td>
                    <a href="#">{{ number_format($total['m_b2e_get']) }}</a>
                </td>
                <td>
                    <a href="#">{{ number_format($total['m_b2c_get']) }}</a>
                </td>
                <td>
                    <a href="#">{{ number_format($total['m_b2b_get']) }}</a>
                </td>
                <td>{{ number_format($total['used']) }}</td>
            </tbody>
        </table>
    </div>
    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-1">
                <thead class="">
                    <tr>
                        <th scope="col" style="width:10px">#</th>
                        <th scope="col">會員編號</th>
                        <th scope="col">姓名</th>
                        <th scope="col">帳號</th>
                        <th scope="col" class="text-end">剩餘點數</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $data->sn }}</td>
                            <td>{!! nl2br($data->name) !!}</td>
                            <td>{{ $data->email }}</td>
                            <th class="text-end">{{ number_format($data->total) }}</th>
                        </tr>
                        <tr>
                            <td class="pt-0 small lh-sm text-center">
                                <div>點數明細</div>
                                <a href="{{ route('cms.customer-dividend.log', ['id' => $data->id]) }}" 
                                    class="icon icon-btn fs-5 text-primary rounded-circle border-0"
                                    data-bs-toggle="tooltip" title="Log">
                                    <i class="bi bi-card-list"></i>
                                </a>
                            </td>
                            <td colspan="4" class="py-0">
                                <table class="table table-sm table-bordered border-secondary small">
                                    <tr class="small border-top-0" style="white-space: normal;">
                                        @foreach ($titleGet as $value1)
                                            <td scope="col" class="wrap lh-sm text-end table-primary">
                                                {{ $value1 }}
                                            </td>
                                        @endforeach
                                        @foreach ($titleUse as $value2)
                                            <td scope="col" class="wrap lh-sm text-end table-danger">
                                                {{ $value2 }}
                                            </td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        @foreach ($fieldGet as $f1)
                                            <td class="text-end table-primary">
                                                {{ number_format($data->formated[$f1]) }}
                                            </td>
                                        @endforeach
                                        @foreach ($fieldUse as $f2)
                                            <td class="text-end table-danger">
                                                {{ number_format($data->formated[$f2]) }}
                                            </td>
                                        @endforeach
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            {{-- 頁碼 --}}
            <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
        </div>
    </div>

    <!-- Modal -->
@endsection

@once
    @push('sub-scripts')
    @endpush
@endonce
