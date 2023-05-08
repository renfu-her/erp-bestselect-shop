@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">消費者紅利點數</h2>
    <form id="search" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">關鍵字</label>
                    <input class="form-control" type="text" name="keyword" placeholder="請輸入姓名或email" value=""
                        aria-label="關鍵字">
                </div>

            </div>
           
            <div class="col">
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>
    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="small">
                    <tr>
                        <th scope="col" style="width:10px">#</th>
                        <th scope="col">會員編號</th>
                        <th scope="col">姓名</th>
                        <th scope="col">帳號</th>
                        <th scope="col">取得點數</th>
                        <th scope="col">使用點數</th>
                        <th scope="col" class="text-center" style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>

                            <td>{{ $data->sn }}</td>
                            <td>{!! nl2br($data->name) !!}</td>
                            <td>{{ $data->email }}</td>
                            <td>{{ $data->get_dividend }}</td>
                            <td>

                                {{ $data->used_dividend }}

                            </td>

                            <td></td>

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
