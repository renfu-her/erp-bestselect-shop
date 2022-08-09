@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">商品管理</h2>

    <form id="search" action="{{ Route('cms.product.index') }}" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">商品名稱</label>
                    <input class="form-control" type="text" name="keyword" value="{{ $cond['keyword'] }}"
                        placeholder="輸入商品名稱">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">負責人</label>
                    <select class="form-select -select2 -multiple" multiple name="user[]" aria-label="負責人"
                        data-placeholder="多選">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @if (in_array($user->id, $cond['user'])) selected @endif>
                                {{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">類型</legend>
                    <div class="px-1 pt-1">
                        @foreach ($productTypes as $key => $type)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="product_type" type="radio"
                                        value="{{ $type[0] }}" @if ($type[0] == $cond['product_type']) checked @endif>
                                    {{ $type[1] }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">耗材</legend>
                    <div class="px-1 pt-1">
                        @foreach ($consumes as $key => $consume)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="consume" type="radio"
                                        value="{{ $consume[0] }}" @if ($consume[0] == $cond['consume']) checked @endif>
                                    {{ $consume[1] }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">公開</legend>
                    <div class="px-1 pt-1">
                        @foreach ($publics as $key => $public)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="public" type="radio"
                                        value="{{ $public[0] }}" @if ($public[0] == $cond['public']) checked @endif>
                                    {{ $public[1] }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">開放通路</legend>
                    <div class="px-1 pt-1">
                        @foreach ($onlineTypes as $key => $types)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="online" type="radio"
                                        value="{{ $types[0] }}" @if ($types[0] == $cond['online']) checked @endif>
                                    {{ $types[1] }}
                                </label>
                            </div>
                        @endforeach
                    </div>

                </fieldset>
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">有否設定宅配？</legend>
                    <div class="px-1 pt-1">
                        @foreach ($hasDelivery as $key => $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="hasDelivery" type="radio"
                                           value="{{ $value[0] }}" @if ($value[0] == $cond['hasDelivery']) checked @endif>
                                    {{ $value[1] }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">有否設定規格說明？</legend>
                    <div class="px-1 pt-1">
                        @foreach ($hasSpecList as $key => $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="hasSpecList" type="radio"
                                           value="{{ $value[0] }}" @if ($value[0] == $cond['hasSpecList']) checked @endif>
                                    {{ $value[1] }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>

            </div>
            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $data_per_page }}" />
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>

    <div class="card shadow p-4 mb-4">
        <div class="row justify-content-end mb-4">
            <div class="col">
                <a href="{{ Route('cms.product.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> 新增商品
                </a>
            </div>
            <div class="col-auto">
                顯示
                <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                    @foreach (config('global.dataPerPage') as $value)
                        <option value="{{ $value }}" @if ($data_per_page == $value) selected @endif>
                            {{ $value }}</option>
                    @endforeach
                </select>
                筆
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="small">
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <th scope="col" class="text-center">編輯</th>
                        <th scope="col">商品名稱</th>
                        <th scope="col">負責人</th>
                        <th scope="col" class="text-center">耗材</th>
                        <th scope="col" class="text-center">宅配</th>
                        {{-- <th scope="col">規格說明</th> --}}
                        <th scope="col" class="text-center">公開</th>
                        <th scope="col" class="text-center">線上</th>
                        <th scope="col" class="text-center">線下</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td class="text-center">
                                <a href="{{ Route('cms.product.edit', ['id' => $data->id], true) }}"
                                    data-bs-toggle="tooltip" title="編輯"
                                    class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </td>
                            <td class="wrap">
                                <div class="lh-1 small text-nowrap">
                                    <span @class(['badge rounded-pill me-2', 
                                        'bg-warning text-dark' => $data->type_title === '組合包商品',
                                        'bg-success' => $data->type_title === '一般商品'])
                                    >{{ $data->type_title === '組合包商品' ? '組合包' : '一般' }}
                                    </span>
                                    <span class="text-secondary">{{ $data->sku }}</span>
                                </div>
                                <div class="lh-base">{{ $data->title }}</div>
                            </td>
                            <td>{{ $data->user_name }}</td>
                            <td class="text-center">
                                @if ($data->consume == '1')
                                    <i class="bi bi-check-lg text-success fs-5"></i>
                                @else
                                    <i class="bi bi-x-lg text-danger"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                @if (isset($data->hasDelivery) && !is_null($data->hasDelivery))
                                    <i class="bi bi-check-lg text-success fs-5"></i>
                                @else
                                    <i class="bi bi-x-lg text-danger"></i>
                                @endif
                            </td>
                            {{-- <td>
                                @if (isset($data->hasSpecList) && !is_null($data->hasSpecList))
                                    <i class="bi bi-check-lg text-success"></i>
                                @endif
                            </td> --}}
                            <td class="text-center">
                                @if ($data->public == '1')
                                    <i class="bi bi-eye-fill fs-5"></i>
                                @else
                                    <i class="bi bi-eye-slash text-secondary fs-5"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($data->online == '1')
                                    <i class="bi bi-check-lg text-success fs-5"></i>
                                @else
                                    <i class="bi bi-x-lg text-danger"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($data->offline == '1')
                                    <i class="bi bi-check-lg text-success fs-5"></i>
                                @else
                                    <i class="bi bi-x-lg text-danger"></i>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            @if ($dataList)
                <div class="mx-3">共 {{ $dataList->lastPage() }} 頁(共找到 {{ $dataList->total() }} 筆資料)</div>
                {{-- 頁碼 --}}
                <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
            @endif
        </div>
    </div>
@endsection
@once
    @push('sub-styles')
        <style>
            .icon.-close_eye+span.label::before {
                content: '不';
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            // 阿眉～
            let selectedUser = @json($cond['user']);
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
        </script>
    @endpush
@endOnce
