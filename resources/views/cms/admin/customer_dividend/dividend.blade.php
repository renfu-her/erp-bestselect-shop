@extends('layouts.main')
@section('sub-content')
    @php
        $categories = \Illuminate\Support\Facades\Request::segments();
        $categoryName = end($categories);
    @endphp
    <h2 class="mb-4">{{ \App\Enums\Discount\DividendCategory::fromValue($categoryName)->description }}點數發放紀錄</h2>
    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox mb-3">
            <table class="table tableList border-bottom">
                <thead class="">
                    <tr>
                        <th scope="col" style="width:10px">#</th>
                        <th scope="col">姓名</th>
                        <th scope="col">會員編號</th>
                        <th scope="col">總發放點數</th>
                        @if($categoryName === 'cyberbiz')
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                        @else
                            <th scope="col">發放點數</th>
                            <th scope="col">備註</th>
                            <th scope="col">發放時間</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $value)
{{--                        {{ dd($value) }}--}}
                        @php
                            $dataGroup = json_decode($value->data);
                            $rows = count($dataGroup) > 0 ? count($dataGroup) + 1 : 2;
                            $striped = $key % 2 === 0 ? 'table-light' : '';
                        @endphp
                        <tr class="{{ $striped }}">
                            <th rowspan="{{ $rows }}" scope="row">{{ $key + 1 }}</th>
                            <td rowspan="{{ $rows }}">
                                <a href="{{ Route('cms.customer.dividend', ['id' => $value->customer_id], true) }}" target="_blank" >
                                    <span class="label">
                                        {{ $value->name }}
                                    </span>
                                    <span class="icon"><i class="bi bi-box-arrow-up-right"></i></span>
                                </a>
                            </td>
                            <td rowspan="{{ $rows }}">{{ $value->sn }}</td>
                            <td rowspan="{{ $rows }}">{{ $value->result }}</td>
                            <td class="p-0 border-bottom-0" height="0"></td>
                            <td class="p-0 border-bottom-0" height="0"></td>
                            <td class="p-0 border-bottom-0" height="0"></td>

                            @if (count($dataGroup) > 0)
                                @foreach($dataGroup as $group)
                                    @if($categoryName === 'cyberbiz')
                                        <tr class="{{ $striped }} -rowspan">
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    @else
                                        <tr class="{{ $striped }} -rowspan">
                                            <td>
                                                {{ $group->dividend }}
                                            </td>
                                            <td>
                                                {{ $group->note }}
                                            </td>
                                            <td>
                                                {{ $group->created_at }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endif
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
