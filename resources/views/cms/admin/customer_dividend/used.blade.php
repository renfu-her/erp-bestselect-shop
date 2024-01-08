@extends('layouts.main')
@section('sub-content')
    @php
        $categories = \Illuminate\Support\Facades\Request::segments();
        $categoryName = end($categories);
    @endphp
    <h2 class="mb-4">{{ \App\Enums\Discount\DividendCategory::fromValue($categoryName)->description }}點數使用紀錄</h2>
    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-1">
                <thead class="">
                    <tr>
                        <th scope="col" style="width:10px">#</th>
                        <th scope="col">姓名</th>
                        <th scope="col">會員編號</th>
                        <th scope="col">使用點數</th>
                        <th scope="col">使用時間</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $value)
{{--                        {{ dd($value->data) }}--}}
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>
                                <a href="{{ Route('cms.customer.dividend', ['id' => $value->customer_id], true) }}" target="_blank" >
                                    <span class="label">
                                        {{ $value->name }}
                                    </span>
                                    <span class="icon"><i class="bi bi-box-arrow-up-right"></i></span>
                                </a>
                            </td>
                            <td>{{ $value->sn }}</td>
                            <td> {{ $value->dividend }}</td>
                            <td> {{ $value->updated_at }}</td>
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


    <div class="col-auto">
        <a href="{{ route('cms.customer-dividend.index') }}" class="btn btn-outline-primary px-4">
            返回列表
        </a>
    </div>

    <!-- Modal -->
@endsection

@once
    @push('sub-scripts')
    @endpush
@endonce
